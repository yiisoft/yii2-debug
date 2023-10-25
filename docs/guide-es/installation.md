Instalación
===========

## Obteniendo el Paquete de Composer

La mejor manera para instalar esta extensión es a través de [composer](https://getcomposer.org/download/).

Ejecuta

```
php composer.phar require --prefer-dist yiisoft/yii2-debug
```

o añade

```
"yiisoft/yii2-debug": "~2.0.0"
```

en la sección require de tu fichero `composer.json`.


## Configurando la aplicación

Para habilitar la extensión, añade estas lineas en tu archivo de configuración para habilitar el modulo debug:

```php
'bootstrap' => ['debug'],
'modules' => [
    'debug' => 'yii\debug\Module',
]
```

Por defecto, el modulo debug sólo trabaja cuando se navega por la web dentro de localhost. Si quieres usar un servidor remoto (staging), añade el parámetro `allowedIps`  para configurar tu ip en la lista (whitelist):

```php
'bootstrap' => ['debug'],
'modules' => [
    'debug' => [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['1.2.3.4', '127.0.0.1', '::1']
    ]
]
```

Si estás usando la opción de URL manager `enableStrictParsing`, añade lo siguiente a tus `reglas`:

```php
'urlManager' => [
    'enableStrictParsing' => true,
    'rules' => [
        // ...
        'debug/<controller>/<action>' => 'debug/<controller>/<action>',
    ],
],
```

> Nota: el depurador almacena información sobre cada petición en el directorio `@runtime/debug`. Si tienes problemas
usando el depurador, tales como errores extraños usandolo, o la barra de herramientas no se muestra o no se muestra ninguna petición,
comprueba si el servidor web tiene los permisos suficientes de acceso a este directorio y a los ficheros localizados
dentro.


### Extra configuración para el logueo y profiling

El Logueo y profiling son simples pero poderosas herramientas que pueden ayudar a entender el flujo de ejecución
del framework y la aplicación. Estas herramientas son usables para los entornos de desarrollo y producción por igual.

Mientras en un entorno de producción, deberías loguear manualmente sólo mensajes significativamente importantes,
como se describe en la [sección de guía de logueo](https://github.com/yiisoft/yii2/blob/master/docs/guide-es/runtime-logging.md). Empeora demasiado el rendimiento loguear continuamente todos los mensajes en producción.

En un entorno de desarrollo, el logueo es mejor, y es especialmente usable para registrar la traza de ejecución.

Para ver los de mensajes de la traza que te ayudarán a entender que sucede dentro del framework, necesitas
asignar el nivel de traza en el archivo de configuración:

```php
return [
    // ...
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0, // <-- aquí
```

Por defecto, el nivel de traza es automáticamente asignado a `3` si Yii se está ejecutando en modo depuración,
determinado por la presencia de la siguiente linea en tu fichero `index.php`:

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

> Nota: Asegúrate de deshabilitar el modo depuración en entornos de producción ya que puede tener un efecto significativo
y adverso del rendimiento. Además el modo depuración puede exponer la información sensible a los usuarios finales.
