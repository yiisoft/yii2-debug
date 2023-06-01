# Database Panel

The Database Panel shows the queries executed by the page and the backtrace which initiated the call.

## Duplicates & Excessive Callers

To assist in debugging and performance optimization the DB panel 'Queries' tab shows "duplicates" 
and the 'Callers' tab the source code origin including the number of calls from the same backtrace. 

Let's dive into their use and differences based on some examples.  
Note: for the sake of simplicity the caller in the examples below is a single file instead of the full backtrace.

***Duplicates*** occur when the exact same query is executed (could be the same or different source code). E.g:

| File:Line   | Query                              | "Duplicates" |    "No. of Calls"     |
|-------------|------------------------------------|:------------:|:---------------------:|
| User.php:20 | `SELECT * FROM user WHERE id=123;` |      2       | + 1 for `User.php:20` |
| Test.php:55 | `SELECT * FROM user WHERE id=123;` |      2       | + 1 for `Test.php:55` |

Results in 2 duplicates (`SELECT * FROM user WHERE id=123;`) and 2 callers  (`User.php:80` making 1 call and `Test.php:55` making 1 call).

***No. of Calls*** increase when the exact same source code makes a DB call (could be the same or different queries). E.g:

| File:Line   | Query                              | "Duplicates" |    "No. of Calls"     |
|-------------|------------------------------------|:------------:|:---------------------:|
| User.php:75 | `SELECT * FROM user WHERE id=123;` |      0       | + 1 for `User.php:75` |
| User.php:75 | `SELECT * FROM user WHERE id=456;` |      0       | + 1 for `User.php:75` |
| User.php:75 | `SELECT * FROM user WHERE id=789;` |      0       | + 1 for `User.php:75` |

Results in 0 duplicates and 1 caller (`User.php:75`) making 3 calls.

A ***combination of both duplicates and repeating calls*** is also possible E.g:

| File:Line   | Query                              | "Duplicates" |    "No. of Calls"     |
|-------------|------------------------------------|:------------:|:---------------------:|
| User.php:80 | `SELECT * FROM user WHERE id=123;` |      2       | + 1 for `User.php:80` |
| User.php:80 | `SELECT * FROM user WHERE id=456;` |      0       | + 1 for `User.php:80` |
| User.php:80 | `SELECT * FROM user WHERE id=789;` |      0       | + 1 for `User.php:80` |
| Test.php:60 | `SELECT * FROM user WHERE id=123;` |      2       | + 1 for `Test.php:60` |

Results in 2 duplicates (`SELECT * FROM user WHERE id=123;`) and 2 callers (`User.php:80` making 3 calls and `Test.php:60` making 1 call).


### Configuring Excessive Callers Threshold

Repeated DB calls from the same backtrace is often an indication that code needs to be optimized (e.g. related models need to be *eager loaded*).
In order to be marked an "Excessive Caller" the number of DB calls needs to exceed the `excessiveCallerThreshold`,
these "Excessive Callers" will be highlighted on the 'Callers' tab.

In case you have implemented your own logic for DB query execution, for example extended the `yii\db\Command`,
that code would be seen as the caller and likely be incorrectly considered an "Excessive Caller".  
In order to avoid this you can add files and/or folders to the `ignoredPathsInBacktrace` list.

```php
$config['modules']['debug'] = [
    'class' => 'yii\debug\Module',
    'panels' => [
        'db' => [
            'class' => 'yii\debug\panels\DbPanel',
            'criticalQueryThreshold' => 1000, // Show warning in case the total number of queries exceed this number.
            'excessiveCallerThreshold' => 10, // Increase the "Excessive Caller" threshold
            'ignoredPathsInBacktrace' => [
                '@common/components/db/MyCustomDbCommand', // Ignore custom DB Command 
            ],
        ],
    ],
];
```

> Note: Both `$criticalQueryThreshold` and `$excessiveCallerThreshold` can be disabled by setting their value to `null`.  
> Changes in `$excessiveCallerThreshold` will only be reflected in new requests.
