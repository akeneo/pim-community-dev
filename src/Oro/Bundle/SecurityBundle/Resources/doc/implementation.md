Implementation
========

Currently, the application has two types of ACL extensions: Actions(Capabilities) and Entities.


You can use @AclAncestor annotation to protect controller actions.

``` php
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor; #required for AclAncestor annotation
...
/**
 * @AclAncestor("myentity_view")
 */
public function viewAction()
```

or check in code directly with [securityFacade service](#securityFacade)

``` php
$this->securityFacade->isGranted('myentity_view')
```

 **Capabilities**:

Additional resources that are not related to an entity, e.g. Configuration, Search etc.

 - Using acl.yml file from MyBundle/Resource/config/acl.yml:

``` yml
can_do_something:
    label: Do something
    type: action
    group_name: "Some Group"
    bindings: ~
```

Than it can be used in @AclAncestor annotation
``` php
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor; #required for AclAncestor annotation
...
/**
 * @AclAncestor("can_do_something")
 */
public function somethingAction()
```

or check in code directly with [securityFacade service](#securityFacade)

``` php
$this->securityFacade->isGranted('can_do_something')
```

If you'd like to bind acl resource to specific controller action, you can use bindings:

``` yml
can_do_something_specific:
    label: Do something
    type: action
    group_name: "Some Group"
    bindings:
        - {  class: someClass, method: someMethod}
```

In this case, when someMethod of someClass is called, can_do_something_specific premission will be checked.

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
