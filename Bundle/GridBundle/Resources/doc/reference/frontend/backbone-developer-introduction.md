Backbone Developer Introduction
-------------------------------

[Backgrid.js](http://wyuenho.github.com/backgrid/) is used as a basic library in OroGridBundle. It's JS modules extended from [Backgrid.js](http://wyuenho.github.com/backgrid/) modules to provide basic functionality of Grid widget. This library built using Backbone.js, and thus can be easily extended. If you don't familiar with [backbone.js](http://backbonejs.org/) look this reference http://backbonejs.org/.

Backbone.js provides several types of entities in the application:

* **View** - mix of View and Controller in classic MVC pattern
* **Model** - is a data container, behaves as active record and responsible for synchronining data with storage
* **Collection** - models composite, iterator, supports mass operations with models
* **Router** - component that allows you to implement the functionality of client side pages history by changing using URL hash fragments (#page). For purposes of routing History object is used. It serves as a global router (per frame) to handle hashchange events or pushState, match the appropriate route, and trigger callbacks

It should be noted that there might be also entities of other types if any type doesn't fit the requirements. In addition, there is a module of Events that is mixin of all Backbone modules. It gives object the ability to bind and trigger custom named events.
