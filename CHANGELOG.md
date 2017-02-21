Yii Framework 2 debug extension Change Log
==========================================

2.0.10 under development
------------------------

- Enh #208: All identity models get converted to arrays when saving User panel data now, not just ActiveRecord models (brandonkelly)
- Enh #208: Identity model packaging for User panels is now done in an `identityData()` method, making it easier for subclasses to customize (brandonkelly) 


2.0.9 February 21, 2017
-----------------------

- Bug #195: Fixed failure when user model has timestamp behavior attached (sam002)
- Bug #199: Do not use user panel in case component isn't properly defined in the application (samdark)
- Bug #200: Fixed error in user panel when RBAC role or permission contains non-string data (samdark)


2.0.8 February 19, 2017
-----------------------

- Bug #82: Fixed debug crashing when there's a closure in log message (samdark)
- Bug #176: Use module's real ID instead of hardcoded "debug" (samdark)
- Enh #34: Added memory graph to timeline panel (bashkarev)
- Enh #174: Added routing panel (bashkarev, samdark)
- Enh #179: Increased request time logging accuracy and precision (samdark)
- Enh #181: Added user panel (pana1990)
- Enh #185: Added meta tag to prevent indexing of debug by search engines in case it's exposed (aminkt, samdark)
- Enh #196: Added language information to config panel (cebe)


2.0.7 November 24, 2016
-----------------------

- Bug #61: Fixed toolbar not to be cached by using renderDynamic (dynasource)
- Bug #93: Fixed `AssetPanel` error when bundle `$js` or `$css` contained `jsOptions` overrides (Razzwan, samdark)
- Bug #99: Avoid serializing php7 errors (zuozp8)
- Bug #111: Fixed `LogTarget` to work properly when tests are ran via Codeception (samdark, nlmedina)
- Bug #120: Fixed toolbar height changing when opened/closed and when using bootstrap (nkovacs)
- Bug #148: Don't animate iframe needlessly when window is resized. (nkovacs)
- Bug #150: Fixed "Cannot read property 'replaceChild' of null" error (BetsuNo)
- Bug #152: Fixed log search to work with non-scalar values (samdark)
- Bug #160: Remove height as it prevents the background from stretching, causing unreadable overlapping texts over background (dynasource)
- Bug #168: Fixed wrong toggle button direction (fps01)
- Enh #8: Added ability to configure default sorting and filtering for Database panel (laszlovl)
- Enh #27: Adjusted sorting defaults, removed row numbers from database, log and profiling panels (samdark)
- Enh #58: Added timeline panel (bashkarev)
- Enh #97: Added AJAX requests handling (bashkarev)
- Enh #105: Enhanced `ConfigPanel` to detect and report memcached extension presence (samdark)
- Enh #115: Make the default panel configurable and set it to `log` (mikehaertl)
- Enh #117: Added ability to customize the logo with `Module::setYiiLogo()` (brandonkelly)
- Enh #143: Added application version display at `ConfigPanel` (klimov-paul)
- Enh #145: The error and warning labels of the log section on the summary bar now link directly to the log page filtered by log level type (rhertogh)
- Enh #162: Added ability to config the trace file and line number (thiagotalma)
- Enh: Mouse wheel click, or Ctrl+Click opens debugger in new tab (silverfire)
- Enh: `yii\debug\Module::defaultVersion()` implemented to pick up 'yiisoft/yii2-debug' extension version (klimov-paul)


2.0.6 March 17, 2016
--------------------

- Bug #41: Debug toolbar was unable to work without asset manager, removed `ToolbarAsset` class (samdark)
- Bug #51: Explain wasn't displayig all data available (lichunqiang)
- Bug #66: Fixed debug panel not working inside applications with response format different from HTML (creocoder, cebe)
- Bug #70: Exception was throwed when `UrlManager::ruleConfig` class was setted with `yii\rest\UrlRule` (lichunqiang)
- Bug: Fixed error when `Yii::$app->db` is not an instance of `yii\db\Connection` (cebe, jafaripur)
- Bug: Fixed exception when no data was recorded for db and profiling panel (cebe, jafaripur)
- Enh #44: Improved display of memory usage to use 3 decimals (dynasource)
- Enh #47: LogTarget storage directory is now created recursively if it does not exist (thiagotalma)
- Enh #63: Enhanced reliablity of request panel in case session is misconfigured (arisk)
- Enh #67: Ability to change permissions for debugger data files and directories (mg-code)
- Enh #83: Debug toolbar now works at the page in async manner (JiLiZART)


2.0.5 August 06, 2015
---------------------

- Bug #33: Fixed `LogTarget::collect()` to call `export()` in a proper way (cornernote)
- Bug #7305: Logging of Exception objects resulted in failure of the logger and no debug data was present (cebe)
- Bug #9112: Fixed initial state of debug toolbar placeholder to prevent "blink" on loading (samdark)
- Bug #9169: Fixed incorrect toolbar image mime causing XML5605 errors in IE console (samdark)
- Enh #16: Added ability to EXPLAIN queries in Database panel for MySQL, SQLite, PostgreSQL and Cubrid (laszlovl, samdark)
- Enh #19: Mark selected log item in dropdown list with bold font and an arrow (idMolotov)
- Enh #25: Make use of full screen width in debug toolbar backend (dynasource, cebe)
- Enh #36: Added check for EXPLAIN support in DbPanel (webdevsega)
- Enh: More compact toolbar (samdark)
- Enh: Display colorful status at index page (samdark)
- Enh: More readable format for date and time at index page (samdark)
- Enh: Toolbar script and styles are now properly registered instead of just echoed (samdark)
- Enh: Toolbar data URL is now HTML-escaped producing valid HTML (samdark)


2.0.4 May 10, 2015
------------------

- Bug #7222: Improved debug toolbar display in rtl pages (mohammadhosain, cebe, samdark)
- Enh #7655: Added ability to filter access by hostname (thiagotalma)
- Enh #7746: Background color of request selector is now choosen based on the current requests status (githubjeka, cebe)


2.0.3 March 01, 2015
--------------------

- Bug #6903: Fixed display issue with phpinfo() table (kalayda, cebe)
- Bug #7222: Debug toolbar wasn't displayed properly in rtl pages (mohammadhosain, johonunu, samdark)
- Enh #6890: Added ability to filter by query type (pana1990)


2.0.2 January 11, 2015
----------------------

- Bug #4820: Fixed reading incomplete debug index data in case of high request concurrency (martingeorg, samdark)
- Chg #6572: Allow panels to stay even if they do not receive any debug data (qiangxue)


2.0.1 December 07, 2014
-----------------------

- Bug #5402: Debugger was not loading when there were closures in asset classes (samdark)
- Bug #5745: Gii and debug modules may cause 404 exception when the route contains dashes (qiangxue)
- Enh #5600: Allow configuring debug panels in `yii\debug\Module::panels` as panel class name strings (qiangxue)
- Enh #6113: Improved configuration and request UI (schmunk42)
- Enh: Made `DefaultController::getManifest()` more robust against corrupt files (cebe)


2.0.0 October 12, 2014
----------------------

- no changes in this release.


2.0.0-rc September 27, 2014
---------------------------

- Bug #1263: Fixed the issue that Gii and Debug modules might be affected by incompatible asset manager configuration (qiangxue)
- Bug #3956: Debug toolbar was affecting flash message removal (samdark)
- Bug #4812: Fixed search filter (samdark)
- Bug #5126: Fixed text body and charset not being set for multipart mail (nkovacs)
- Enh #2299: Date and time in request list is now never wrapped (samdark)
- Enh #3088: The debug module will manage their own URL rules now (qiangxue)
- Enh #3103: debugger panel is now not displayed when printing a page (githubjeka)
- Enh #3108: Added `yii\debug\Module::enableDebugLogs` to disable logging debug logs by default (qiangxue)
- Enh #3810: Added "Latest" button on panels page (thiagotalma)
- Enh #4031: Http status codes were hardcoded in filter (sdkiller)
- Enh #5089: Added asset debugger panel (arturf, qiangxue)

2.0.0-beta April 13, 2014
-------------------------

- Bug #1783: Using VarDumper::dumpAsString() instead var_export(), because var_export() does not handle circular references. (djagya)
- Bug #1504: Debug toolbar isn't loaded successfully in some environments when xdebug is enabled (qiangxue)
- Bug #1747: Fixed problems with displaying toolbar on small screens (cebe)
- Bug #1827: Debugger toolbar is loaded twice if an action is calling `run()` to execute another action (qiangxue)
- Enh #1667: Added mail panel (Ragazzo, 6pblcb)
- Enh #2006: Added total queries count monitoring (o-rey, Ragazzo)

2.0.0-alpha, December 1, 2013
-----------------------------

- Initial release.
