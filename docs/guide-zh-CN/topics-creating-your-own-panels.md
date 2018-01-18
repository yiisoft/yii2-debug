创建自己的面板
========================

工具栏和调试器都具有高度可配置性和可定制性。这样做，您可以创建您自己的面板，
收集并显示您想要的具体数据。下面我们将介绍创建一个简单的自定义面板的过程：

- 收集请求时渲染的视图；
- 在工具栏中显示视图的数量；
- 允许您在调试器中查看视图名称。

假设您使用了基本的项目模板。

首先我们需要在 `panels/ViewsPanel.php` 中实现 `Panel` 类：

```php
<?php
namespace app\panels;

use yii\base\Event;
use yii\base\View;
use yii\base\ViewEvent;
use yii\debug\Panel;


class ViewsPanel extends Panel
{
    private $_viewFiles = [];

    public function init()
    {
        parent::init();
        Event::on(View::className(), View::EVENT_BEFORE_RENDER, function (ViewEvent $event) {
            $this->_viewFiles[] = $event->sender->getViewFile();
        });
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Views';
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary()
    {
        $url = $this->getUrl();
        $count = count($this->data);
        return "<div class=\"yii-debug-toolbar__block\"><a href=\"$url\">Views <span class=\"yii-debug-toolbar__label yii-debug-toolbar__label_info\">$count</span></a></div>";
    }

    /**
     * {@inheritdoc}
     */
    public function getDetail()
    {
        return '<ol><li>' . implode('<li>', $this->data) . '</ol>';
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        return $this->_viewFiles;
    }
}
```

以上代码的工作流程是：

1. 在任何控制器动作运行之前执行 `init`。此方法是在控制器动作执行过程中从最佳位置收集数据。
2. 执行控制器动作之后调用 `save`。此方法返回的数据将存储在数据文件中。如果这个方法没有返回，面板
   将不会被渲染。
3. 数据文件中的数据被加载到 `$this->data`。对于工具栏来说，代表最新的数据。对于调试器来说，此属性可以被设置为
   从任何以前的数据文件中读取。
4. 工具栏从 `getSummary` 获取其内容。在这里，我们展示的渲染视图文件的数量。
   调试器使用 `getDetail` 达到同样的目的。

现在是时候告诉调试器来使用新的面板了。在 `config/web.php` 中，调试配置信息更改为：

```php
if (YII_ENV_DEV) {
    // 配置调整为“开发”环境
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'panels' => [
            'views' => ['class' => 'app\panels\ViewsPanel'],
        ],
    ];

// ...
```

好了。现在我们没有写太多代码就拥有了另一个有用的面板了。
