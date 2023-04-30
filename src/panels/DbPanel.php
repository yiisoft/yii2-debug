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
 * @property-read array $profileLogs
 * @property-read string $summaryName Short name of the panel, which will be use in summary.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DbPanel extends Panel
{
    /**
     * @var int the threshold for determining whether the request has involved
     * critical number of DB queries. If the number of queries exceeds this number,
     * the execution is considered taking critical number of DB queries.
     */
    public $criticalQueryThreshold;
    /**
     * @var int Number of DB calls the same line of code can make before considered a "repeating caller".
     */
    public $repeatingCallerCallsThreshold = 5;
    /**
     * @var string[] Files and/or paths defined here will be ignored by the determination of repeating callers.
     * Hint: You can use path aliasses here.
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

        return Yii::$app->view->render('panels/db/summary', [
            'timings' => $this->calculateTimings(),
            'panel' => $this,
            'queryCount' => $queryCount,
            'queryTime' => $queryTime,
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
        $callerDataProvider = $this->generateRepeatingQueryCallersDataProvider($models);

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
            $this->_timings = Yii::getLogger()->calculateTimings(isset($this->data['messages']) ? $this->data['messages'] : []);

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
        return $this->getLogMessages(Logger::LEVEL_PROFILE, $this->dbEventNames);
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
            $repeatingCallers = $this->countRepeatingQueryCallerCals($timings);

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
                    'repeatingCallerCalls' => $repeatingCallers[$dbTiming['traceHash']]
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
     * Counts the number of times the same line of code makes a DB query.
     *
     * @param $timings
     * @return array Number of DB calls indexed by the hash of the caller.
     * @since 2.1.23
     */
    public function countRepeatingQueryCallerCals($timings)
    {
        $query = ArrayHelper::getColumn($timings, 'traceHash');

        return array_count_values($query);
    }

    /**
     * Creates an ArrayDataProvider for the repeating DB query callers.
     *
     * @param array $modelData
     * @return ArrayDataProvider
     * @since 2.1.23
     */
    public function generateRepeatingQueryCallersDataProvider($modelData)
    {
        $repeatingCallers = [];
        foreach ($modelData as $data) {
            if ($data['repeatingCallerCalls'] >= $this->repeatingCallerCallsThreshold) {
                if (!array_key_exists($data['traceHash'], $repeatingCallers)) {
                    $repeatingCallers[$data['traceHash']] = [
                        'trace' => $data['trace'],
                        'repeatingCallerCalls' => $data['repeatingCallerCalls'],
                        'totalDuration' => 0,
                        'queries' => []
                    ];
                }
                $repeatingCallers[$data['traceHash']]['totalDuration'] += $data['duration'];
                $repeatingCallers[$data['traceHash']]['queries'][] = [
                    'timestamp' => $data['timestamp'],
                    'duration' => $data['duration'],
                    'query' => $data['query'],
                    'type' => $data['type'],
                    'seq' => $data['seq'],
                ];
            }
        }

        return new ArrayDataProvider([
            'allModels' => $repeatingCallers,
            'pagination' => false,
            'sort' => [
                'attributes' => ['repeatingCallerCalls', 'totalDuration'],
                'defaultOrder' => ['repeatingCallerCalls' => SORT_DESC],
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
     * Check if given queries count is critical according settings.
     *
     * @param int $count queries count
     * @return bool
     */
    public function isQueryCountCritical($count)
    {
        return (($this->criticalQueryThreshold !== null) && ($count > $this->criticalQueryThreshold));
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
