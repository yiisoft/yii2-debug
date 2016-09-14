<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\models\search;

use yii\debug\components\search\Filter;
use yii\debug\components\search\matchers\GreaterThanOrEqual;
use yii\debug\components\TimelineDataProvider;

/**
 * Search model for timeline data.
 *
 * @author Dmitriy Bashkarev <dmitriy@bashkarev.com>
 * @since 2.0.7
 */
class Timeline extends Base
{

    /**
     * @var string method attribute input search value
     */
    public $category;

    /**
     * @var integer duration attribute input search value
     */
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'duration' => 'Duration ≥'
        ];
    }

    /**
     * @param array $params $params an array of parameter values indexed by parameter names
     * @param array $models $models data to return provider for
     * @param array $timestamps timestamps data
     * @return TimelineDataProvider
     */
    public function search($params, $models, $timestamps)
    {
        $dataProvider = new TimelineDataProvider([
            'start' => $timestamps[0],
            'end' => $timestamps[1],
            'duration' => $timestamps[2],
            'allModels' => $models,
            'sort' => [
                'attributes' => ['category', 'timestamp']
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $filter = new Filter();
        $this->addCondition($filter, 'category', true);
        if ($this->duration > 0) {
            $filter->addMatcher('duration', new GreaterThanOrEqual(['value' => $this->duration / 1000 ]));
        }
        $dataProvider->allModels = $filter->filter($models);

        return $dataProvider;
    }

}