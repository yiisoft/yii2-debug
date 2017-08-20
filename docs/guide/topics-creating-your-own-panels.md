Creating your own panels
========================

Both the toolbar and debugger are highly configurable and customizable. To do so, you can create your own panels that collect
and display the specific data you want. Below we'll describe the process of creating a simple custom panel that:

- collects the views rendered during a request;
- shows the number of views rendered in the toolbar;
- allows you to check the view names in the debugger.

The assumption is that you're using the basic project template.

First we need to implement the `Panel` class in `panels/ViewsPanel.php`:

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
     * @inheritdoc
     */
    public function getName()
    {
        return 'Views';
    }

    /**
     * @inheritdoc
     */
    public function getSummary()
    {
        $url = $this->getUrl();
        $count = count($this->data);
        return "<div class=\"yii-debug-toolbar__block\"><a href=\"$url\">Views <span class=\"yii-debug-toolbar__label yii-debug-toolbar__label_info\">$count</span></a></div>";
    }

    /**
     * @inheritdoc
     */
    public function getDetail()
    {
        return '<ol><li>' . implode('</li><li>', $this->data) . '</li></ol>';
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        return $this->_viewFiles;
    }
}
```

The workflow for the code above is:

1. `init` is executed before any controller action is run. This method is the best place to attach handlers that will collect data during the controller action's execution.
2. `save` is called after controller action is executed. The data returned by this method will be stored in a data file. If nothing is returned by this method, the panel
   won't be rendered.
3. The data from the data file is loaded into `$this->data`. For the toolbar, this will always represent the latest data. For the debugger, this property may be set to be read from any previous data file as well.
4. The toolbar takes its contents from `getSummary`. There, we're showing the number of view files rendered. The debugger uses
   `getDetail` for the same purpose.

Now it's time to tell the debugger to use the new panel. In `config/web.php`, the debug configuration is modified to:

```php
if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'panels' => [
            'views' => ['class' => 'app\panels\ViewsPanel'],
        ],
    ];

// ...
```

That's it. Now we have another useful panel without writing much code.
