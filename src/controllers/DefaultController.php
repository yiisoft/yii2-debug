<?php
/**
 * @link      http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

namespace yii\debug\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\debug\models\search\Debug;
use yii\web\Response;

/**
 * Debugger controller provides browsing over available debug logs.
 *
 * @see    \yii\debug\Panel
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since  2.0
 */
class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $layout = 'main';
    /**
     * @var \yii\debug\Module owner module.
     */
    public $module;
    /**
     * @var array the summary data (e.g. URL, time)
     */
    public $summary;


    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = [];
        foreach ($this->module->panels as $panel) {
            $actions = array_merge($actions, $panel->actions);
        }

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $searchModel = new Debug();
        $manifest = $this->module->getDataStorage()->getDataManifest();
        $dataProvider = $searchModel->search($_GET, $manifest);

        // load latest request
        $tags = array_keys($manifest);
        $tag = reset($tags);
        $this->loadData($tag);

        return $this->render('index', [
            'panels' => $this->module->panels,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'manifest' => $manifest,
        ]);
    }

    /**
     * @param string|null $tag   debug data tag.
     * @param string|null $panel debug panel ID.
     *
     * @return mixed response.
     * @throws NotFoundHttpException if debug data not found.
     * @see \yii\debug\Panel
     */
    public function actionView($tag = null, $panel = null)
    {
        $manifest = $this->module->getDataStorage()->getDataManifest();

        if ($tag === null) {
            $tags = array_keys($manifest);
            $tag = reset($tags);
        }
        $this->loadData($tag);
        if (isset($this->module->panels[$panel])) {
            $activePanel = $this->module->panels[$panel];
        } else {
            $activePanel = $this->module->panels[$this->module->defaultPanel];
        }

        if ($activePanel->hasError()) {
            Yii::$app->errorHandler->handleException($activePanel->getError());
        }

        return $this->render('view', [
            'tag' => $tag,
            'summary' => $this->summary,
            'manifest' => $manifest,
            'panels' => $this->module->panels,
            'activePanel' => $activePanel,
        ]);
    }

    public function actionToolbar($tag)
    {
        $this->loadData($tag, 5);

        return $this->renderPartial('toolbar', [
            'tag' => $tag,
            'panels' => $this->module->panels,
            'position' => 'bottom',
        ]);
    }

    public function actionDownloadMail($file)
    {
        $filePath = Yii::getAlias($this->module->panels['mail']->mailPath) . '/' . basename($file);

        if ((mb_strpos($file, '\\') !== false || mb_strpos($file, '/') !== false) || !is_file($filePath)) {
            throw new NotFoundHttpException('Mail file not found');
        }

        return Yii::$app->response->sendFile($filePath);
    }

    /**
     * @param string $tag      debug data tag.
     * @param int    $maxRetry maximum numbers of tag retrieval attempts.
     *
     * @throws NotFoundHttpException if specified tag not found.
     */
    public function loadData($tag, $maxRetry = 0)
    {
        // retry loading debug data because the debug data is logged in shutdown function
        // which may be delayed in some environment if xdebug is enabled.
        // See: https://github.com/yiisoft/yii2/issues/1504
        for ($retry = 0; $retry <= $maxRetry; ++$retry) {
            $manifest = $this->module->getDataStorage()->getDataManifest($retry > 0);
            if (isset($manifest[$tag])) {
                $data=$this->module->getDataStorage()->getData($tag);
                $exceptions = $data['exceptions'];
                foreach ($this->module->panels as $id => $panel) {
                    if (isset($data[$id])) {
                        $panel->tag = $tag;
                        $panel->load(unserialize($data[$id]));
                    }
                    if (isset($exceptions[$id])) {
                        $panel->setError($exceptions[$id]);
                    }
                }
                $this->summary = $data['summary'];

                return;
            }
            sleep(1);
        }

        throw new NotFoundHttpException("Unable to find debug data tagged with '$tag'.");
    }
}