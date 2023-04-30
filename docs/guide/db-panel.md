# Database Panel

The Database Panel shows the queries executed by the page and which line of code initiated the call.

## Duplicates & Repeating Callers

To assist in debugging and performance optimization the DB panel 'Queries' tab shows "duplicates" and the 'Repeated Callers' tab call from the same line of code. 
Let's dive into their use and differences based on some examples.

***Duplicates*** occur when the exact same query is executed (could be the same or different source code). E.g:

| File:Line  | Query                              | "Duplicates" | "Repeating caller" |
|------------|------------------------------------|:------------:|:------------------:|
|User.php:20 | `SELECT * FROM user WHERE id=123;` |       2      |         -          |
|Test.php:55 | `SELECT * FROM user WHERE id=123;` |       2      |         -          |

Results in 2 duplicates (`SELECT * FROM user WHERE id=123;`) and 0 repeating callers.

***Repeating calls*** occur when the exact same source code makes a DB call (could be the same or different queries). E.g:

| File:Line  | Query                              | "Duplicates" | "Repeating caller" |
|------------|------------------------------------|:------------:|:------------------:|
|User.php:75 | `SELECT * FROM user WHERE id=123;` |      0       |         ✓          |
|User.php:75 | `SELECT * FROM user WHERE id=456;` |      0       |         ✓          |
|User.php:75 | `SELECT * FROM user WHERE id=789;` |      0       |         ✓          |

Results in 0 duplicates and 1 repeating caller (`User.php:75`) making 3 calls.

A ***combination of both duplicates and repeating calls*** is also possible E.g:

| File:Line   | Query                              | "Duplicates" | "Repeating caller" |
|-------------|------------------------------------|:------------:|:------------------:|
| User.php:80 | `SELECT * FROM user WHERE id=123;` |      2       |         ✓          |
| User.php:80 | `SELECT * FROM user WHERE id=456;` |      0       |         ✓          |
| User.php:80 | `SELECT * FROM user WHERE id=789;` |      0       |         ✓          |
| Test.php:55 | `SELECT * FROM user WHERE id=123;` |      2       |         0          |

Results in 2 duplicates (`SELECT * FROM user WHERE id=123;`) and 1 repeating caller (`User.php:80`) making 3 calls.


### Configuring Repeating Callers

Repeated DB calls from the same line of code is often an indication that code needs to be optimized (e.g. related models need to be *eager loaded*).
In order to be marked a "Repeating Caller" the number of DB calls needs to exceed the `repeatingCallerCallsThreshold`,
callers that remain under this threshold are ignored and won't show up on the "Repeating Callers" tab.

In case you have implemented your own logic for DB query execution, for example extended the `yii\db\Command`,
that code would be seen as the caller and likely be incorrectly considered a "Repeating Caller".  
In order to avoid this you can add files and/or folders to the `ignoredPathsInBacktrace` list.

```php
$config['modules']['debug'] = [
    'class' => 'yii\debug\Module',
    'panels' => [
        'db' => [
            'class' => 'yii\debug\panels\DbPanel',
            'repeatingCallerCallsThreshold' => 10, // Increase the "Repeating Callers" threshold
            'ignoredPathsInBacktrace' => [
                '@common/components/db/MyCustomDbCommand', // Ignore custom DB Command 
            ],
        ],
    ],
];
```
