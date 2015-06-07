Створення власних панелей
=========================

Як панель налагодження так і налагоджувач можна сконфігурувати та налаштувати. Щоб зробити це, ви можете створити власні панелі, що
будуть збирати та відображати особливі дані, які ви забажаєте. Нижче буде описаний процес створення простої панелі, яка:

- збирає представлення, сформовані протягом запиту;
- відображає кількість сформованих представлень у панелі налагодження;
- дозволяє перевірити імена представлень у налагоджувачі.

Припускається, що ви використовуєте базовий шаблон проекту.

Спершу необхідно реалізувати клас `Panel` в `panels/ViewsPanel.php`:

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
        return "<div class=\"yii-debug-toolbar-block\"><a href=\"$url\">Views <span class=\"label\">$count</span></a></div>";
    }

    /**
     * @inheritdoc
     */
    public function getDetail()
    {
        return '<ol><li>' . implode('<li>', $this->data) . '</ol>';
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

Робочий процес вищезазначеного коду наступний:

1. Виконується `init` перед запуском будь-якої дії контролера. Цей метод - найкраще місце для приєднання обробників, які будуть збирати дані протягом виконання дії контролера.
2. Викликається `save` після виконання дії контролера. Дані, що повертаються цим методом, будуть збережені у файлі даних. Якщо нічого не повернуто цим методом, то панель
   не буде сформована.
3. Дані з файлу даних завантажуються у `$this->data`. Для панелі налагодження - це завжди останні дані. Для налагоджувача - цій властивості може бути встановлено значення, також прочитане з будь-якого попереднього файлу даних.
4. Панель налагодження приймає вміст з `getSummary`, який відображає кількість сформованих файлів представлень. Налагоджувач
   для цих цілей використовує `getDetail`.

Тепер прийшов час вказати налагоджувачу використовувати нову панель. У `config/web.php` конфігурація налагодження змінена на:

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

От і все. Тепер ви маєте іншу корисну панель без написання великої кількості коду.
