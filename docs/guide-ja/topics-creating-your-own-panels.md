あなた自身のパネルを作る
========================

ツールバーとデバッガは、ともに、高い構成可能性とカスタマイズ性を持っています。
これらをカスタマイズするために、あなた自身のパネルを作成して、あなたが必要とする特定のデータを収集して表示することが出来ます。
以下において、簡単なカスタムパネルを作るプロセスを説明します。そのパネルは以下の機能を持つものとします。

- リクエストの間にレンダリングされたビューを収集する
- ツールバーにレンダリングされたビューの数を表示する
- デバッガでビューの名前を確認することが出来る

なお、あなたがベーシックプロジェクトテンプレートを使用しているものと仮定しています。

最初に、`panels/ViewsPanel.php` で `Panel` クラスを実装する必要があります。

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

上記のコードのワークフローは以下のとおりです。

1. 全てのコントローラのアクションが走る前に `init` が実行されます。
   コントローラのアクションが実行される間にデータを収集するハンドラをアタッチするには、このメソッドが最適の場所です。
2. コントローラのアクションが実行された後に `save` が呼ばれます。
   このメソッドによって返されたデータは、データファイルに保存されます。
   このメソッドが何も返さなかった場合には、パネルは表示されません。
3. データファイルからのデータは `$this->data` にロードされます。
   ツールバーの場合は、これは常に最新のデータを表します。
   デバッガの場合は、このプロパティを以前のどのデータファイルからでも読み出すことが出来ます。
4. ツールバーはその内容を `getSummary` から取得します。
   そこではレンダリングされたビューの数を表示します。
   デバッガは同じ目的のために `getDetail` を使用します。

さあ、それでは、デバッガに新しいパネルを使うように教えましょう。
`config/web.php` で、デバッガの構成を次のように変更します。

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

以上です。これで、たいしてコードを書くこともなく、もう一つの便利なパネルを手に入れました。
