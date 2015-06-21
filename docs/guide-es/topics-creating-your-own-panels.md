Creando tus propios paneles
===========================

Tanto la barra de herramientas como el depurador son altamente configurables y personalizables. Para ello, puedes
crear tus propios paneles que recojan y muestren los datos específicos que quieras. A continuación describiremos
el proceso de creación de un simple panel personalizable que:

- recoja las vistas renderizadas durante una petición;
- muestra el número de vistas renderizadas en la barra de herramientas;
- permitirte comprobar el nombre de las vistas en el depurador;

Supondremos que estás usando la plantilla básica del projecto.

Primero necesitamos implementar la clase `Panel` en `panels/ViewsPanel.php`:

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

El flujo de trabajo para el código de arriba es:

1. `init` se está ejecutando antes de que se ejecute cualquier acción del controlador. Este método es el mejor
lugar para anexar los manejadores que recogen los datos durante la ejecución de la acción del controlador.
2. `save` es llamado después de ejecutarse la acción del controlador. Los datos devueltos por este método serán
almacenados en un archivo de datos. Si no se devuelve nada por este método, el panel no será renderizado.
3. Los datos del archivo de datos se cargan con `$this->data`. Para la barra de herramientas, esto siempre
representará los últimos datos. Para el depurador, esta propiedad puede ser asignada para ser leída desde
cualquier archivo de datos anterior así.
4. La barra de herramientas coge el contenido de `getSummary`. Ya está, estamos viendo el número de archivos
de vistas renderizadas. El depurador usa `getDetail` para el mismo propósito.

Ahora es el momento de decirle al depurador que use el nuevo panel. En `config/web.php`, la configuración del
depurador se modifica aquí:

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

Eso es todo. Ahora tenemos otro panel usable sin escribir demasiado código.
