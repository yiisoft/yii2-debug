Installation
============

## Getting Composer package

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-debug
```

or add

```
"yiisoft/yii2-debug": "~2.1.0"
```

to the require section of your `composer.json` file.


## Configuring application

To enable extension, add these lines to your configuration file to enable the debug module:

```php
'bootstrap' => ['debug'],
'modules' => [
    'debug' => [
        '__class' => yii\debug\Module::class,
    ],
]
```

By default, the debug module only works when browsing the website from localhost. If you want to use it on a remote (staging)
server, add the parameter `allowedIPs` to the configuration to whitelist your IP:

```php
'bootstrap' => ['debug'],
'modules' => [
    'debug' => [
        '__class' => yii\debug\Module::class,
        'allowedIPs' => ['1.2.3.4', '127.0.0.1', '::1']
    ]
]
```

If you are using `enableStrictParsing` URL manager option, add the following to your `rules`:

```php
'urlManager' => [
    'enableStrictParsing' => true,
    'rules' => [
        // ...
        'debug/<controller>/<action>' => 'debug/<controller>/<action>',
    ],
],
```

> Note: the debugger stores information about each request in the `@runtime/debug` directory. If you have problems using
> the debugger, such as weird error messages when using it, or the toolbar not showing up or not showing any requests, check
> whether the web server has enough permissions to access this directory and the files located inside.


### Extra configuration for logging and profiling

Logging and profiling are simple but powerful tools that may help you to understand the execution flow of both the
framework and the application. These tools are useful for development and production environments alike.

While in a production environment, you should log only significantly important  messages manually, as described in
[logging guide section](https://github.com/yiisoft/yii2/blob/master/docs/guide/runtime-logging.md). It hurts performance too much to continue to log all messages in production.

In a development environment, the more logging the better, and it's especially useful to record the execution trace.

In order to see the trace messages that will help you to understand what happens under the hood of the framework, you
need to set the trace level in the configuration file:

```php
return [
    // ...
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0, // <-- here
```

By default, the trace level is automatically set to `3` if Yii is running in debug mode, as determined by the presence of
the following line in your `index.php` file:

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

> Note: Make sure to disable debug mode in production environments since it may have a significant and adverse performance
effect. Further, the debug mode may expose sensitive information to end users.

### Configuring Database panel

Database panel default sorting and filtering can be configured like the following:

```php
$config['modules']['debug'] = [
    '__class' => yii\debug\Module::class,
    'panels' => [
        'db' => [
            '__class' => yii\debug\panels\DbPanel::class,
            'defaultOrder' => [
                'seq' => SORT_ASC
            ],
            'defaultFilter' => [
                'type' => 'SELECT'
            ]
        ],
    ],
];
```

### Extra configuration for opening in IDE's

Wouldn't it be nice to be able to open files directly from the debug trace? 

Well, you can!
With a few settings you're ready to go!


#### Windows

##### 1) Create a WScript file open_phpstorm.js:
Create a file `C:\Program Files (x86)\JetBrains\open_phpstorm.js` (example for PhpStorm)
with the following content:

```js

var settings = {
	// Set to 'true' (without quotes) if run on Windows 64bit. Set to 'false' (without quotes) otherwise.
	x64: true,

	// Set to folder name, where PhpStorm was installed to (e.g. 'PhpStorm')
	folder_name: 'PhpStorm 2016.2.1',

	// Set to window title (only text after dash sign), that you see, when switching to running PhpStorm instance
	window_title: 'PhpStorm 2016.2.1',

	// In case your file is mapped via a network share and paths do not match.
	// eg. /var/www will can replaced with Y:/
	projects_basepath: '',
	projects_path_alias: ''
};


// don't change anything below this line, unless you know what you're doing
var	url = WScript.Arguments(0),
	match = /^ide:\/\/(?:.+)file:\/\/(.+)&line=(\d+)$/.exec(url),
	project = '',
	editor = '"C:\\' + (settings.x64 ? 'Program Files' : 'Program Files (x86)') + '\\JetBrains\\' + settings.folder_name + '\\bin\\PhpStorm.exe"';

if (match) {

	var	shell = new ActiveXObject('WScript.Shell'),
		file_system = new ActiveXObject('Scripting.FileSystemObject'),
		file = decodeURIComponent(match[1]).replace(/\+/g, ' '),
		search_path = file.replace(/\//g, '\\');

	if (settings.projects_basepath != '' && settings.projects_path_alias != '') {
		file = file.replace(new RegExp('^' + settings.projects_basepath), settings.projects_path_alias);
	}

	while (search_path.lastIndexOf('\\') != -1) {
		search_path = search_path.substring(0, search_path.lastIndexOf('\\'));

		if(file_system.FileExists(search_path+'\\.idea\\.name')) {
			project = search_path;
			break;
		}
	}

	if (project != '') {
		editor += ' "%project%"';
	}

	editor += ' --line %line% "%file%"';

	var command = editor.replace(/%line%/g, match[2])
						.replace(/%file%/g, file)
						.replace(/%project%/g, project)
						.replace(/\//g, '\\');

	shell.Exec(command);
	shell.AppActivate(settings.window_title);
}
```

##### 2) Create a registry file and execute this file 

Create a registry file `C:\Program Files (x86)\JetBrains\open_phpstorm.reg` (example for PhpStorm)
with the following content and make sure the paths are correct:

```windows.reg
Windows Registry Editor Version 5.00

[HKEY_CLASSES_ROOT\ide]
@="\"URL:ide Protocol\""
"URL Protocol"=""

[HKEY_CLASSES_ROOT\ide\shell\open\command]
@="wscript \"C:\\Program Files (x86)\\JetBrains\\open_phpstorm.js\" %1"
```

Now you are able to use the ide:// protocol in your browser. 

When you click such a link, the IDE will automatically open the file and move the cursor to the corresponding line.

##### Disable links
IDE links for traces are created by default. You have to set the property `yii\debug\Module::traceLink` to
 false to render a textual line only.

```php
<?php

...
'modules' => [
    'debug' => [
        '__class' => yii\debug\Module::class,
        'traceLink' => false
    ]
]

...
```

### Virtualized or dockerized

If your application is run under a virtualized or dockerized environment, it is often the case that the application's base path is different inside of the virtual machine or container than on your host machine. For the links work in those situations, you can configure `traceLine` like this (change the path to your app):

```php
'traceLine' => function($options, $panel) {
    $filePath = str_replace(Yii::$app->basePath, '~/path/to/your/app', $options['file']);
    return strtr('<a href="ide://open?url=file://{file}&line={line}">{text}</a>', ['{file}' => $filePath]);
},
```

### Switching Users

You can use log in as any user and reset to back to your primary user. In order to enable the feature you need to configure access permissions in the `UserPanel` config. By default access is denied to everyone.

```php
return [
    'bootstrap' => ['debug'],
    'modules' => [
        'debug' => [
            '__class' => yii\debug\Module::class,
            'panels' => [
                'user' => [
                    '__class' => yii\debug\panels\UserPanel::class,
                    'ruleUserSwitch' => [
                        'allow' => true,
                        'roles' => ['manager'],
                    ]
                ]
            ]
        ],
        // ...
    ],
    ...
];
```

For details see [Guide Authorization](http://www.yiiframework.com/doc-2.0/guide-security-authorization.html).
