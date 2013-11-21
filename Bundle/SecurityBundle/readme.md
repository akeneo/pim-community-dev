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

 - [http://symfony.com/doc/current/cookbook/security/acl.html ]
 - [http://symfony.com/doc/current/cookbook/security/acl_advanced.html ]

In additional Oro allows you to protect data on different levels:

 - **System**: Allows to gives a user a permissions to access to all records within the system.
 - **Organization**: Allows to gives a user a permissions to access to all records within the organization, regardless of the business unit hierarchical level to which a record belongs or the user is assigned to.
 - **Division**: Allows to gives a user a permissions to access to records in all business units are assigned to the user and all business units subordinate to business units are assigned to the user.
 - **Business Unit**: Allows to gives a user a permissions to access to records in all business units are assigned to the user.
 - **User**: Allows to gives a user a permissions to access to own records and records that are shared with the user.

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
 - If a user has the **VIEW Account on Division level** privilege, this user can view all accounts in all business units assigned to this user, but cannot view accounts in any child business units.
 - If a user has the **VIEW Account on Subordinate Business Unit level** privilege, this user can view all accounts in all business units assigned to this user, and all accounts in any child business units of these business units.
 - If a user has the **VIEW Account on Organization level** privilege, this user can view all accounts in all organizations assigned to this user.
 - If a user has the **VIEW Account on System level** privilege, this user can view all accounts regardless of which organizations this user or an account belongs.

### Implementation

Currently, the application has two types of ACL extensions: Actions(Capabilities) and Entities.

**Entity**

Resources, that gives control on entity manipulations (View, Edit, Delete etc.).

To mark an entity as ACL protected, the next config to the @Configurable annotation in entity class should be added:

``` php
/**
...
* @Configurable(
*  defaultValues={
    ...
*      "security"={
*          "type"="ACL",
           "permissions"="All"
*          "group_name"="SomeGroup"
*      }
    ...
*  }
* )
...
 */
 class MyEntity
```
**permissions** parameter is used is used to specify the access list for the entity. This parameter is optional.
If it is not specified, or is "All", it is considered that the entity access to all available security permissions.

You can create your list of accesses. For example, the string "VIEW;EDIT" will set the permissions parameters for the entity for viewing and editing.

**group_name** parameter is used to group entities by groups in UI edit page. Now this parameter is not in use.

You can use @Acl and @AclAncestor annotations to protect controller actions.

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

Additional resources that are not related to an entity, e.g. Configuration, Search etc.

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

To check if ACL system is enabled in current application, there is a **isAclEnabled** function that return true or false result.

**EXAMPLES OF ACL MANAGER USAGE**

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

**getSid function** return security identity for given parameter. Parameters of function can be:

 - **string**. In this case security identity whill be taken form the role with name setted as parameter;
 - **RoleInterface**. Return SID for current role object
 - **UserInterface**.  Creates a user security identity from a UserInterface
 - **UserInterface**. Creates a user security identity from a TokenInterface
 
**getOid** function constructs an ObjectIdentity for the given domain object or based on the given descriptor.

The descriptor is a string in the following format: "ExtensionKey:Class"

Exapmles:

 - getOid($object);
 - getOid('Entity:AcmeBundle\SomeClass')
 - getOid('Entity:AcmeBundle:SomeEntity')
 - getOid('Action:Some Action')
 
**getMaskBuilder** function gets the new instance of the mask builder which can be used to build permission bitmask for an object with the given object identity.

 As one ACL extension can support several masks (each mask is stored in own ACE; an example of ACL extension which supports several masks is 'Entity' extension - see EntityAclExtension class) you need to provide any permission supported by expected mask builder instance.

Also you can omit $permission argument. In this case a default mask builder is returned.

For example the following calls return the same mask builder:

``` php
$manager->getMaskBuilder($manager->getOid('entity: AcmeBundle:AcmeEntity'))
$manager->getMaskBuilder($manager->getOid('entity: AcmeBundle:AcmeEntity'), 'VIEW')
$manager->getMaskBuilder($manager->getOid('entity: AcmeBundle:AcmeEntity'), 'DELETE')
``` 

because VIEW, CREATE, EDIT, DELETE, ASSIGN and SHARE permissions are supported by EntityMaskBuilder class and it is the default mask builder for 'Entity' extension.

If you sure that some ACL extension supports only one mask, you can omit $permission argument as well.

For example the following calls are identical:

``` php
$manager->getMaskBuilder($manager->getOid('action: Acme Action'))
$manager->getMaskBuilder($manager->getOid('entity: Acme Action'), 'EXECUTE')
``` 

**setPermission**  function updates or creates object-based or class-based ACE with the given attributes.

If the given object identity represents a domain object the object-based ACE is set;
otherwise, class-based ACE is set.
If the given object identity represents a "root" ACL the object-based ACE is set.

``` php
$manager->setPermission(
    $sid,
    $oid,
    $mask
);
``` 
With **setFieldPermission** function you can update or create object-field-based or class-field-based ACE with the given attributes.

If the given object identity represents a domain object the object-field-based ACE is set.
Otherwise, class-field-based ACE is set.

**deletePermission** and **deleteFieldPermission** functions allow to delete object-based or class-based (deletePermission) and object-field-based or class-field-based (deleteFieldPermission) ACE with the given attributes.

**deleteAllPermissions** and **deleteAllFieldPermissions** deletes all object-based or class-based and object-field-based or class-field-based ACEs for the given security identity
     
To get all the registered ACL extensions registered in system (now it is a entity and action extensions) you can use **getAllExtensions** function.

After the setting new ACL permissions to an object, the changes must be saved. It can be done with **flush** function.

If an object is not get its own access rights, then the access check is on the root object. To get an ObjectIdentity is used for grant default permissions, can be used the **getRootOid** function with ACL extension key as parameter.

To get the ACLs that belong to the given object identities can be used **findAcls** function. **deleteAcl** function delete an ACL for the given ObjectIdentity.

