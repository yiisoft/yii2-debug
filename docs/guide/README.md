Debug Extension for Yii 2
=========================

This extension provides a debugger for Yii 2 applications. When this extension is used,
a debugger toolbar will appear at the bottom of every page. The extension also provides
a set of standalone pages to display more detailed debug information.

The toolbar displays information about the currently opened page, while the debugger can be used to analyze data you've
previously collected (i.e., to confirm the values of variables).

Out of the box these tools allow you to:

- quickly get the framework version, PHP version, response status, current controller and action, performance info and
  more via toolbar;
- browse the application and PHP configuration;
- view the request data, request and response headers, session data, and environment variables;
- see, search, and filter the logs;
- view any profiling results;
- view the [database queries](db-panel.md) executed by the page;
- view the emails sent by the application.

All of this information will be available per request, allowing you to revisit the information for past requests as well.

Getting Started
---------------

* [Installation](installation.md)

Additional topics
-----------------

* [Creating your own panels](topics-creating-your-own-panels.md)
