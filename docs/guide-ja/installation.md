インストール
============

## Composer パッケージを取得する

このエクステンションをインストールするのに推奨される方法は [composer](http://getcomposer.org/download/) によるものです。

下記のコマンドを実行してください。

```
php composer.phar require --prefer-dist yiisoft/yii2-debug
```

または、あなたの `composer.json` ファイルの `require` セクションに、下記を追加してください。

```
"yiisoft/yii2-debug": "~2.0.0"
```


## アプリケーションを構成する

エクステンションを有効にするためには、構成情報ファイルに以下の行を追加してデバッグ・モジュールを有効にします。

```php
'bootstrap' => ['debug'],
'modules' => [
    'debug' => 'yii\debug\Module',
]
```

デフォルトでは、デバッグ・モジュールはウェブ・サイトをローカル・ホストから閲覧した場合にだけ動作します。
これをリモート・サーバ (ステージング・サーバ) で使いたい場合は、パラメータ `allowedIPs` を構成情報に追加して、あなたの IP をホワイトリストに加えてください。

```php
'bootstrap' => ['debug'],
'modules' => [
    'debug' => [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['1.2.3.4', '127.0.0.1', '::1']
    ]
]
```

URL マネージャで `enableStrictParsing` オプションを使っている場合は、`rules` に次の行を追加してください。

```php
'urlManager' => [
    'enableStrictParsing' => true,
    'rules' => [
        // ...
        'debug/<controller>/<action>' => 'debug/<controller>/<action>',
    ],
],
```

> Note: デバッガは各リクエストに関する情報を `@runtime/debug` ディレクトリに保存します。
> デバッガを使用するのに問題が生じたとき、例えば、デバッガを使おうとするとおかしなエラー・メッセージが出たり、ツール・バーが表示されなかったり、リクエストの情報が何も表示されなかったりしたときは、ウェブ・サーバがこのディレクトリとその中に置かれるファイルに対して十分なアクセス権限を持っているかどうかを確認してください。


### ロギングとプロファイリングのための追加の構成

ロギングとプロファイリングは、フレームワークとアプリケーションの両方の実行フローを理解するのを助けてくれる、単純ながら強力なツールです。これらのツールは、開発環境でも本番環境でも役に立ちます。

本番環境では、[ロギング](https://github.com/yiisoft/yii2/blob/master/docs/guide-ja/runtime-logging.md) のガイドの節で説明されているように、著しく重要なメッセージを手動でログに取るだけにとどめるべきです。
本番環境で全てのメッセージをログに取り続けるのは、パフォーマンスへの損害が大きすぎます。

開発環境では、ログは多く取れば取るほど良いでしょう。とりわけ、実行トレースの記録は有用です。

フレームワークのフードの下で何が起っているかを理解する手助けとなるトレース・メッセージを見るためには、構成情報ファイルでトレース・レベルを設定する必要があります。

```php
return [
    // ...
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0, // <-- ここ
```

デフォルトでは、Yii がデバッグモードで走っている場合のトレース・レベルは自動的に `3` に設定されます。
デバッグモードは `index.php` ファイルに次の行が存在することによって決定されます。

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

> Note: デバッグ・モードはパフォーマンスに著しい悪影響を及ぼし得ますので、本番環境では必ずデバッグ・モードを無効にしてください。
更に、デバッグ・モードは公開すべきでない情報をエンドユーザに曝露することがあり得ます。

### データベース・パネルを構成する

データベース・パネルのデフォルトの並べ替えとフィルタリングを次のようにして構成することが出来ます。

```php
$config['modules']['debug'] = [
    'class' => 'yii\debug\Module',
    'panels' => [
        'db' => [
            'class' => 'yii\debug\panels\DbPanel',
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

### IDE で開くための追加の設定

デバッグ・トレースから直接にファイルを開くことが出来たら素敵だと思いませんか？

なんと、出来るんです。
ほんの少し設定をすれば、準備完了です。


#### Windows

##### 1) open_phpstorm.js という WScript ファイルを作成する
次の内容を持つ `C:\Program Files (x86)\JetBrains\open_phpstorm.js` と言うファイル (PhpStorm の場合の例) を作ります。

```js

var settings = {
	// 64-bit Windows で実行する場合は 'true' (引用符無し) に設定。そうでなければ 'false' (引用符無し)
	x64: true,

	// PhpStorm がインストールされたフォルダ名に設定 (例: 'PhpStorm')
	folder_name: 'PhpStorm 2016.2.1',

	// PhpStorm のインスタンスの実行に切り替えたときに表示されるウィンドウのタイトル ('-' 以降のテキストのみ) を設定
	window_title: 'PhpStorm 2016.2.1',

	// ファイルがネットワーク共有にマップされており、パスが一致しない場合に
	// 例えば、/var/www を Y:/ にマップ
	projects_basepath: '',
	projects_path_alias: ''
};


// 何をしているか知っている場合以外は、ここから下の行は、少しも変えないこと
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

##### 2) レジストリ・ファイルを作成して実行する

次の内容を持つレジストリ・ファイル `C:\Program Files (x86)\JetBrains\open_phpstorm.reg` (example for PhpStorm)
を作成して実行します。パスが正しいことを確認して下さい。

```windows.reg
Windows Registry Editor Version 5.00

[HKEY_CLASSES_ROOT\ide]
@="\"URL:ide Protocol\""
"URL Protocol"=""

[HKEY_CLASSES_ROOT\ide\shell\open\command]
@="wscript \"C:\\Program Files (x86)\\JetBrains\\open_phpstorm.js\" %1"
```

これで、ブラウザで ide:// プロトコルを使うことが出来るようになります。

そのようなリンクをクリックすると、IDE が自動的にファイルを開いて、対応する行にカーソルを移動します。

##### リンクを無効化する

トレースのための IDE リンクはデフォルトで作成されます。
テキスト行だけを表示したい場合は、プロパティ `yii\debug\Module::traceLink` を `false` に設定しなければなりません。

```php
<?php

...
'modules' => [
    'debug' => [
        'class' => 'yii\debug\Module',
        'traceLink' => false
    ]
]

...
```

### 仮想化または Docker 化

あなたのアプリケーションが仮想化または Docker 化せれた環境で走っている場合は、仮想マシンまたはコンテナの中とホスト・マシンの間で、アプリケーションのベース・パスが異なることがよくあります。
このような環境でリンクが動作するようにするために、`traceLine` を下記のように構成することが出来ます。

```php
'traceLine' => function($options, $panel) {
    $filePath = str_replace(Yii::$app->basePath, '~/path/to/your/app', $options['file']);
    return strtr('<a href="ide://open?url=file://{file}&line={line}">{text}</a>', ['{file}' => $filePath]);
},
```

### ユーザを切り替える

任意のユーザとしてログインし、再び元のユーザに戻るということが出来ます。
この機能を有効にするためには、`UserPanel` 構成情報のアクセス許可を構成する必要があります。
デフォルトではこの機能は全てのユーザに対してアクセスが禁止されています。

```php
return [
    'bootstrap' => ['debug'],
    'modules' => [
        'debug' => [
            'class' => 'yii\debug\Module',
            'panels' => [
                'user' => [
                    'class'=>'yii\debug\panels\UserPanel',
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

詳細は [権限付与のガイド](http://www.yiiframework.com/doc-2.0/guide-security-authorization.html) を参照して下さい。
