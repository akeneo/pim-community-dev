JavaScript Tools and Libraries
----------------------------

This article contains description of all JavaScript tools and libraries in OroUIBundle.

### Table of Contents

* [Oro](#oro)
* [Oro.Registry](#ororegistry)
* [Oro.BootstrapModal](#orobootstrapmodal)
* [Libraries](#libraries)


### Oro

Oro is the global namespace for all JS widgets. Also it contains several useful functions.

**Public methods**

* **packToQueryString** (Object object) - converts source object to query string to send on server;
* **unpackFromQueryString** (String query) - reverse action to packToQueryString function, converts query string to object;
* **invertKeys** (Object object, Object keys) - replaces key in object according to data from keys;
* **isEqualsLoosely** (value1, value2) - compares any type of values for equality;
* **deepClone** (Object value) - clones source value with all references.


### Oro.BootstrapModal

Oro.BootstrapModal extends Backbone.BootstrapModal (implemented as Backbone Bootstrap Modal) and replaces modal template.


### Libraries

Following list includes all libraries which are used from OroUIBundle.

* Twitter Bootstrap - http://twitter.github.io/bootstrap/
* LESS - http://lesscss.org/
* Backbone - http://backbonejs.org/
* Underscore - http://underscorejs.org/
* jsTree - http://www.jstree.com/
* JQuery Plugins:
    * Timepicker Addon - https://github.com/trentrichardson/jQuery-Timepicker-Addon
    * Custom Scrollbar
    * Mouse Wheel - http://brandonaaron.net/code/mousewheel/docs
    * Numeric - http://www.texotela.co.uk/code/jquery/numeric/
    * Placeholder - http://mths.be/placeholder
    * Uniform
