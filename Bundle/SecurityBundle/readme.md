OroSecurityBundle
========================
The `OroSecurityBundle` provides flexible security model which allows to protect data integrity and privacy. The goals of this bundle are as follows:

 - Provide users with the access only to the appropriate levels of information that is required to do their jobs.
 - Categorize users by role and restrict access based on those roles.
 - Support data sharing so that users can be granted access to records that they do not own for a specified collaborative effort.
 - Prevent a user's access to records the user does not own or share.

The Oro security model is based on the Symfony standard security model, but adds some significant modifications to allow protect data on different levels.

The next sections describes Oro ACL (Access Control Lists) security.

ACL
---

At first it is important to say that all benefits of Symfony ACL based security is supported by Oro as well. It means that access can be granted/denied on the following scopes:

 - **Class-Scope**: Allows to set permissions for all objects with the same type.
 - **Object-Scope**: Allows to set permissions for one specific object.
 - **Class-Field-Scope**: Allows to set permissions for all objects with the same type, but only to a specific field of the objects.
 - **Object-Field-Scope**: Allows to set permissions for a specific object, and only to a specific field of that object.

Detailed information about Symfony ACL based security model you can read in the Symfony documentation:

 - [http://symfony.com/doc/current/cookbook/security/acl.html]
 - [http://symfony.com/doc/current/cookbook/security/acl_advanced.html]

In additional Oro allows you to protect data on different levels:

 - **System**: Allows to gives a user a permissions to access to all records within the system.
 - **Organization**: Allows to gives a user a permissions to access to all records within the organization, regardless of the business unit hierarchical level to which a record belongs or the user is assigned to.
 - **Subordinate Business Unit**: Allows to gives a user a permissions to access to records in all business units are assigned to the user and all business units subordinate to business units are assigned to the user.
 - **Business Unit**: Allows to gives a user a permissions to access to records in all business units are assigned to the user.
 - **User**: Allows to gives a user a permissions to access to own records and records that are shared with the user.

 `*` **NOTE: Currently only System level is supported**

Also the following permissions are supported:

 - **VIEW**: Controls whether a user is allowed to view a record.
 - **CREATE**: Controls whether a user is allowed to create a record.
 - **EDIT**: Controls whether a user is allowed to modify a record.
 - **DELETE**: Controls whether a user is allowed to delete a record.
 - **ASSIGN**: Controls whether a user is allowed to change an owner of a record. For example assign a record to another user.
 - **SHARE**: Controls whether the user can share a record with another user.

 `*` **NOTE: SHARE functionality isn't implemented yet, so SHARE permissions are not used**

### Examples

 - If a user has **VIEW Contact on User level** privileges, this user can view all contacts he/she owns.
 - If a user has the **VIEW Account on Business Unit level** privilege, this user can view all accounts in all business units assigned to this user, but cannot view accounts in any child business units.
 - If a user has the **VIEW Account on Subordinate Business Unit level** privilege, this user can view all accounts in all business units assigned to this user, and all accounts in any child business units of these business units.
 - If a user has the **VIEW Account on Organization level** privilege, this user can view all accounts in all organizations assigned to this user.
 - If a user has the **VIEW Account on System level** privilege, this user can view all accounts regardless of which organizations this user or an account belongs.

### Implementation

There are 2 groups of ACL resources:

**Entity**

Resources, that gives control on entity manipulations (View, Edit, Delete etc.).

To add entity to ACL, the next config to the @Configurable annotation in entity class should be added:

``` php
/**
...
* @Configurable(
*  defaultValues={
    ...
*      "security"={
*          "type"="ACL",
*          "group_name"="SomeGroup"
*      }
    ...
*  }
* )
...
 */
 class MyEntity
```

There are 2 ways to declare entity class based permissions:

 - Using @Acl annotation:

``` php
use Oro\Bundle\SecurityBundle\Annotation\Acl; #required for Acl annotation
...
/**
 * @Acl(
 *      id="myentity_view",
 *      type="entity",
 *      class="MyBundle:MyEntity",
 *      permission="VIEW"
 * )
 */
public function viewAction()
```
This means that the view action is executable if VIEW premission is granted to MyEntity

 - Using acl.yml file from MyBundle/Resource/config/acl.yml:

``` yml
myentity_view:
    type: entity
    class: MyBundle:MyEntity
    permission="VIEW"
```
Than it can be used in @AclAncestor annotation
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

Additional resources that are not related to specific entity, e.g. Configuration, Search etc.

There are 2 ways to declare capability permissions:

 - Using @Acl annotation:

``` php
use Oro\Bundle\SecurityBundle\Annotation\Acl; #required for Acl annotation
...
/**
* @Acl(
*      id="can_do_something",
*      type="action",
*      label="Do something",
*      group_name="Some Group"
* )
*/
public function somethingAction()
```

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

If you'd like to bind acl resource to specific class methods, you can use bindings:

``` yml
can_do_something_specific:
    label: Do something
    type: action
    group_name: "Some Group"
    bindings:
        - {  class: someClass, method: someMethod}
```

In this case, when someMethod of someClass is called, can_do_something_specific premission will be checked.

###UI

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

###ACL manager

ACL manager (`oro_security.acl.manager` service) is responsible for internal ACL operations and should be used in case some custom ACL operations are needed.

**EXAMPLES**

Setting VIEW and EDIT class-based pemissions to `MyBundle:MyEntity` class for Manager Role

``` php
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
...
public function setAclManager(AclManager $manager)
{
    //Injecting Acl Manager
    $this->manager = $manager;
}
...
public function setViewEditPermissions()
{
    $sid = $manager->getSid('ROLE_MANAGER');
    $oid = $manager->getOid('Entity:MyBundle:MyEntity');
    $builder = $manager->getMaskBuilder($oid);
    //building necessary permissions mask, see Acl/Extension/EntityMaskBuilder class for a list of permission constants
    $mask = $builder->add('VIEW_SYSTEM')->add('EDIT_SYSTEM')->get();

    $manager->setPermission(
        $sid,
        $oid,
        $mask
    );
    //saving permissions
    $manager->flush();
}
...
```

 Granting `some_action_id` capability for Manager Role

``` php
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
...
public function setAclManager(AclManager $manager)
{
    //Injecting Acl Manager
    $this->manager = $manager;
}
...
public function setExecutePermissions()
{
    $sid = $manager->getSid('ROLE_MANAGER');
    $oid = $manager->getOid('Action:some_action_id');
    $builder = $manager->getMaskBuilder($oid);
    //building necessary permissions mask, for actions only EXECUTE mask is currently available
    $mask = $builder->add('EXECUTE')->get();

    $manager->setPermission(
        $sid,
        $oid,
        $mask
    );
    //saving permissions
    $manager->flush();
}
...
```
