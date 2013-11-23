UI
========

Current UI implementation allows to define permissions for user roles. For Entities it's managed with Entity/Permission matrix, for Capabilities, it's just a list of available resources.

If a user has several roles assigned, and we are checking user's permission to some resource, if **any** of user's roles grants access to this resource then access is also granted to this user.

<a name="securityFacade"></a>
#### Security Facade

oro_security.security_facade is a public service that covers most of ACL check cases and it should be injected in case some custom ACL checks are required.

There are 2 public methods:

``` php
isClassMethodGranted($class, $method)
```
Checks if an access to the given method of the given class is granted

and

``` php
isGranted($attributes[, $object])
```
Checks if an access to the resource defined by `$attributes` and `$object(optional)` is granted

**$attributes** can be a role name(s), permission name(s), an ACL annotation id or some other identifiers depending on registered security voters.

**$object** can be a descriptor('Entity:MyBundle:MyEntity'), entity object or instance of ObjectIdentity

**Examples**

Checking access to some ACL annotation resource

``` php
$this->securityFacade->isGranted('some_resource_id')
```
Checking VIEW access to the entity by class name

``` php
$this->securityFacade->isGranted('VIEW', 'Entity:MyBundle:MyEntity' );
```

Checking ASSIGN access to the entity object

``` php
$this->securityFacade->isGranted('ASSIGN', $myEntity);
```

Checking access is performed in the following way: **Object-Scope**->**Class-Scope**->**Default Permissions**.

For example, we are checking View permission to $myEntity object of MyEntity class. When we call

``` php
$this->securityFacade->isGranted('VIEW', $myEntity);
```
first ACL for `$myEntity` object is checked, if nothing is found, then it checks ACL for `MyEntity` class and if no records are found, finally checks the Default(root) permissions.
