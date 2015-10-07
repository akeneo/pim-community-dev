Links in YAML:
=======
It's possible to use static method call, service method call and class constant access in YAML datagrid configuration.
These links will be called by SystemAwareResolver while building datagrid in datagrid manager.

Link types:
==========

Service call
-----
```
@oro_email.grid.query_builder->getChoicesQuery
```
Call method getChoicesQuery with datagrid name and YAML configuration key as arguments from oro_email.grid.query_builder service.

Static method call
-----
```
%oro_datagrid.some.class%::testStaticCall
or
Acme\Bundle\DemoBundle\SomeClass::testStaticCall
```
Class name can be defined in container's parameters or specified directly.

Constant
----
```
%oro_datagrid.some.class%::TEST
Acme\Bundle\DemoBundle\SomeClass::TEST
```
PHP is_callable used to determine if it's callable or should be treated as constant.

If it's not callable and no constant exists with such name in the class, value became unchanged.

Service injection
-----
```
some_key: @some.serviceID
```