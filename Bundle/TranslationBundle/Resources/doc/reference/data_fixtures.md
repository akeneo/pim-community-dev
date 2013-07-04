Data Fixtures
-------------

Article provides information about data fixtures for translatable entities.


### Classes Description

#### AbstractTranslatableEntityFixture

This class is intended for creating of translatable entities. It gathers translation files,
defines existing locales and provides service methods to perform translation.
Descended classes must define method "loadEntities".

Constants:

* **ENTITY\_DOMAIN** - translation domain that contains translations for translatable entities,
default value is "entities";
* **DOMAIN\_FILE\_REGEXP** - regular expression for matching of appropriate translation files and extracting of locale.

Methods:

* **load** - method from Doctrine AbstractFixture, entry point for data fixture run,
sets translator property and runs loadEntities method;
* **setContainer** - method form ContainerAwareInterface, sets container property;
* **loadEntities** - abstract method, must be specified in descendant classes to load entities;
* **getDomainFileRegExp** - returns formed regular expression based on source expression and current domain;
* **getTranslationLocales** - parses all translation files and searches files that matches formed regular expression,
returns list of locales with appropriate translations;
* **translate** - translates string for specified ID, prefix and locale;
parameters and domain also can be specified manually;
* **getTranslationId** - forms translation ID based on entity string ID and entity prefix.
