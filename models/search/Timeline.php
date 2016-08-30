<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\models\search;

use yii\data\ArrayDataProvider;
use yii\debug\components\search\Filter;
use yii\debug\components\search\matchers\GreaterThanOrEqual;

/**
 * Search model for current request timeline.
 *
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 * @since 2.0
 */
class Timeline extends Base
{

    /**
     * @var string method attribute input search value
     */
    public $category;

    public $duration = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category', 'duration'], 'safe'],
        ];
    }

    /**
     * Returns data provider with filled models. Filter applied if needed.
     *
     * @param array $params an array of parameter values indexed by parameter names
     * @param array $models data to return provider for
     * @return \yii\data\ArrayDataProvider
     */
    public function search($params, $models)
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
            'pagination' => false,
            'sort' => [
                'attributes' => ['category', 'timestamp'],
                'defaultOrder' => [
                    'timestamp' => SORT_ASC,
                ],
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $filter = new Filter();
        $this->addCondition($filter, 'category', true);
        if ($this->duration > 0) {
            $filter->addMatcher('duration', new GreaterThanOrEqual(['value' => $this->duration]));
        }
        $dataProvider->allModels = $filter->filter($models);

        return $dataProvider;
    }

}