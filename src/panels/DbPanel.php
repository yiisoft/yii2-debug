<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\debug\panels;

use Yii;
use yii\base\InvalidConfigException;
use yii\data\ArrayDataProvider;
use yii\debug\models\search\Db;
use yii\debug\Panel;
use yii\helpers\ArrayHelper;
use yii\log\Logger;

/**
 * Debugger panel that collects and displays database queries performed.
 *
 * @property-read array $excessiveCallers The number of DB calls indexed by the backtrace hash of excessive
 * caller(s).
 * @property-read array $profileLogs
 * @property-read string $summaryName Short name of the panel, which will be use in summary.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbPanel extends Panel
{
    /**
     * @var int|null the threshold for determining whether the request has involved
     * critical number of DB queries. If the number of queries exceeds this number,
     * the execution is considered taking critical number of DB queries.
     * If it is `null`, this feature is disabled.
     */
    public $criticalQueryThreshold;
    /**
     * @var int|null the number of DB calls the same backtrace can make before considered an "Excessive Caller".
     * If it is `null`, this feature is disabled.
     * Note: Changes will only be reflected in new requests.
     * @since 2.1.23
     */
    public $excessiveCallerThreshold = 5;
    /**
     * @var string[] the files and/or paths defined here will be ignored in the determination of DB "Callers".
     * The "Caller" is the backtrace lines that aren't included in the `$ignoredPathsInBacktrace`,
     * Yii files are ignored by default.
     * Hint: You can use path aliases here.
     * @since 2.1.23
     */
    public $ignoredPathsInBacktrace = [];
    /**
     * @var string the name of the database component to use for executing (explain) queries
     */
    public $db = 'db';
    /**
     * @var array the default ordering of the database queries. In the format of
     * [ property => sort direction ], for example: [ 'duration' => SORT_DESC ]
     * @since 2.0.7
     */
    public $defaultOrder = [
        'seq' => SORT_ASC
    ];
    /**
     * @var array the default filter to apply to the database queries. In the format
     * of [ property => value ], for example: [ 'type' => 'SELECT' ]
     * @since 2.0.7
     */
    public $defaultFilter = [];
    /**
     * @var array db queries info extracted to array as models, to use with data provider.
     */
    private $_models;
    /**
     * @var array current database request timings
     */
    private $_timings;
    /**
     * @var array current database profile logs
     */
    private $_profileLogs;


    /**
     * @var array of event names used to get profile logs.
     * @since 2.1.17
     */
    public $dbEventNames = ['yii\db\Command::query', 'yii\db\Command::execute'];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->actions['db-explain'] = [
            'class' => 'yii\\debug\\actions\\db\\ExplainAction',
            'panel' => $this,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Database';
    }

    /**
     * @return string short name of the panel, which will be use in summary.
     */
    public function getSummaryName()
    {
        return 'DB';
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary()
    {
        $timings = $this->calculateTimings();
        $queryCount = count($timings);
        $queryTime = number_format($this->getTotalQueryTime($timings) * 1000) . ' ms';
        $excessiveCallerCount = $this->getExcessiveCallersCount();

        return Yii::$app->view->render('panels/db/summary', [
            'timings' => $this->calculateTimings(),
            'panel' => $this,
            'queryCount' => $queryCount,
            'queryTime' => $queryTime,
            'excessiveCallerCount' => $excessiveCallerCount,
        ]);
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function getDetail()
    {
        $searchModel = new Db();

        if (!$searchModel->load(Yii::$app->request->getQueryParams())) {
            $searchModel->load($this->defaultFilter, '');
        }

        $models = $this->getModels();
        $queryDataProvider = $searchModel->search($models);
        $queryDataProvider->getSort()->defaultOrder = $this->defaultOrder;
        $sumDuplicates = $this->sumDuplicateQueries($models);
        $callerDataProvider = $this->generateQueryCallersDataProvider($models);

        return Yii::$app->view->render('panels/db/detail', [
            'panel' => $this,
            'queryDataProvider' => $queryDataProvider,
            'callerDataProvider' => $callerDataProvider,
            'searchModel' => $searchModel,
            'hasExplain' => $this->hasExplain(),
            'sumDuplicates' => $sumDuplicates,
        ]);
    }

    /**
     * Calculates given request profile timings.
     *
     * @return array timings [token, category, timestamp, traces, nesting level, elapsed time]
     */
    public function calculateTimings()
    {
        if ($this->_timings === null) {
            $this->_timings = Yii::getLogger()->calculateTimings(isset($this->data['messages']) ? $this->data['messages'] : $this->getProfileLogs());

            // Parse aliases
            $ignoredPathsInBacktrace = array_map(
                function($path) {
                    return Yii::getAlias($path);
                },
                $this->ignoredPathsInBacktrace
            );

            // Generate hash for caller
            $hashAlgo = in_array('xxh3', hash_algos()) ? 'xxh3' : 'crc32';
            foreach ($this->_timings as &$timing) {
                if ($ignoredPathsInBacktrace) {
                    foreach ($timing['trace'] as $index => $trace) {
                        foreach ($ignoredPathsInBacktrace as $ignoredPathInBacktrace) {
                            if (isset($trace['file']) && strpos($trace['file'], $ignoredPathInBacktrace) === 0) {
                                unset($timing['trace'][$index]);
                            }
                        }
                    }
                }
                $timing['traceHash'] = hash($hashAlgo, json_encode($timing['trace']));
            }
        }

        return $this->_timings;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        return ['messages' => $this->getProfileLogs()];
    }

    /**
     * Returns all profile logs of the current request for this panel. It includes categories specified in $this->dbEventNames property.
     * @return array
     */
    public function getProfileLogs()
    {
        if ($this->_profileLogs === null) {
            $this->_profileLogs = $this->getLogMessages(Logger::LEVEL_PROFILE, $this->dbEventNames);
        }

        return $this->_profileLogs;
    }

    /**
     * Returns total query time.
     *
     * @param array $timings
     * @return int total time
     */
    protected function getTotalQueryTime($timings)
    {
        $queryTime = 0;

        foreach ($timings as $timing) {
            $queryTime += $timing['duration'];
        }

        return $queryTime;
    }

    /**
     * Returns an  array of models that represents logs of the current request.
     * Can be used with data providers such as \yii\data\ArrayDataProvider.
     * @return array models
     */
    protected function getModels()
    {
        if ($this->_models === null) {
            $this->_models = [];
            $timings = $this->calculateTimings();
            $duplicates = $this->countDuplicateQuery($timings);

            foreach ($timings as $seq => $dbTiming) {
                $this->_models[] = [
                    'type' => $this->getQueryType($dbTiming['info']),
                    'query' => $dbTiming['info'],
                    'duration' => ($dbTiming['duration'] * 1000), // in milliseconds
                    'trace' => $dbTiming['trace'],
                    'traceHash' => $dbTiming['traceHash'],
                    'timestamp' => ($dbTiming['timestamp'] * 1000), // in milliseconds
                    'seq' => $seq,
                    'duplicate' => $duplicates[$dbTiming['info']],
                ];
            }
        }

        return $this->_models;
    }

    /**
     * Return associative array, where key is query string
     * and value is number of occurrences the same query in array.
     *
     * @param $timings
     * @return array
     * @since 2.0.13
     */
    public function countDuplicateQuery($timings)
    {
        $query = ArrayHelper::getColumn($timings, 'info');

        return array_count_values($query);
    }

    /**
     * Returns sum of all duplicated queries
     *
     * @param $modelData
     * @return int
     * @since 2.0.13
     */
    public function sumDuplicateQueries($modelData)
    {
        $numDuplicates = 0;
        foreach ($modelData as $data) {
            if ($data['duplicate'] > 1) {
                $numDuplicates++;
            }
        }

        return $numDuplicates;
    }

    /**
     * Counts the number of times the same backtrace makes a DB query.
     *
     * @return array the number of DB calls indexed by the backtrace hash of the caller.
     * @since 2.1.23
     */
    public function countCallerCals()
    {
        $query = ArrayHelper::getColumn($this->calculateTimings(), 'traceHash');

        return array_count_values($query);
    }

    /**
     * Get the backtrace hashes that make excessive DB cals.
     *
     * @return array the number of DB calls indexed by the backtrace hash of excessive caller(s).
     * @since 2.1.23
     */
    public function getExcessiveCallers()
    {
        if ($this->excessiveCallerThreshold === null) {
            return [];
        }

        return array_filter(
            $this->countCallerCals(),
            function ($count) {
                return $count >= $this->excessiveCallerThreshold;
            }
        );
    }

    /**
     * Get the number of excessive caller(s).
     *
     * @return int
     * @since 2.1.23
     */
    public function getExcessiveCallersCount()
    {
        return count($this->getExcessiveCallers());
    }

    /**
     * Creates an ArrayDataProvider for the DB query callers.
     *
     * @param array $modelData
     * @return ArrayDataProvider
     * @since 2.1.23
     */
    public function generateQueryCallersDataProvider($modelData)
    {
        $callers = [];
        foreach ($modelData as $data) {
            if (!array_key_exists($data['traceHash'], $callers)) {
                $callers[$data['traceHash']] = [
                    'trace' => $data['trace'],
                    'numCalls' => 0,
                    'totalDuration' => 0,
                    'queries' => []
                ];
            }
            $callers[$data['traceHash']]['numCalls'] += 1;
            $callers[$data['traceHash']]['totalDuration'] += $data['duration'];
            $callers[$data['traceHash']]['queries'][] = [
                'timestamp' => $data['timestamp'],
                'duration' => $data['duration'],
                'query' => $data['query'],
                'type' => $data['type'],
                'seq' => $data['seq'],
            ];
        }

        return new ArrayDataProvider([
            'allModels' => $callers,
            'pagination' => false,
            'sort' => [
                'attributes' => ['numCalls', 'totalDuration'],
                'defaultOrder' => ['numCalls' => SORT_DESC],
            ],
        ]);
    }

    /**
     * Returns database query type.
     *
     * @param string $timing timing procedure string
     * @return string query type such as select, insert, delete, etc.
     */
    protected function getQueryType($timing)
    {
        $timing = ltrim($timing);
        preg_match('/^([a-zA-z]*)/', $timing, $matches);

        return count($matches) ? mb_strtoupper($matches[0], 'utf8') : '';
    }

    /**
     * Check if given queries count is critical according to the settings.
     *
     * @param int $count queries count
     * @return bool
     */
    public function isQueryCountCritical($count)
    {
        return (($this->criticalQueryThreshold !== null) && ($count > $this->criticalQueryThreshold));
    }

    /**
     * Check if the number of calls by "Caller" is excessive according to the settings.
     *
     * @param int $numCalls queries count
     * @return bool
     */
    public function isNumberOfCallsExcessive($numCalls)
    {
        return (($this->excessiveCallerThreshold !== null) && ($numCalls > $this->excessiveCallerThreshold));
    }

    /**
     * Returns array query types
     *
     * @return array
     * @since 2.0.3
     */
    public function getTypes()
    {
        return array_reduce(
            $this->_models,
            function ($result, $item) {
                $result[$item['type']] = $item['type'];
                return $result;
            },
            []
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        try {
            $this->getDb();
        } catch (InvalidConfigException $exception) {
            return false;
        }

        return parent::isEnabled();
    }

    /**
     * @return bool Whether the DB component has support for EXPLAIN queries
     * @since 2.0.5
     * @throws InvalidConfigException
     */
    protected function hasExplain()
    {
        $db = $this->getDb();
        if (!($db instanceof \yii\db\Connection)) {
            return false;
        }
        switch ($db->getDriverName()) {
            case 'mysql':
            case 'sqlite':
            case 'pgsql':
            case 'cubrid':
                return true;
            default:
                return false;
        }
    }

    /**
     * Check if given query type can be explained.
     *
     * @param string $type query type
     * @return bool
     *
     * @since 2.0.5
     */
    public static function canBeExplained($type)
    {
        return $type !== 'SHOW';
    }

    /**
     * Returns a reference to the DB component associated with the panel
     *
     * @return \yii\db\Connection
     * @since 2.0.5
     * @throws InvalidConfigException
     */
    public function getDb()
    {
        return Yii::$app->get($this->db);
    }
}
