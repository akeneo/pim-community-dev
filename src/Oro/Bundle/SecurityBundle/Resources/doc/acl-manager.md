ACL manager
========

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
