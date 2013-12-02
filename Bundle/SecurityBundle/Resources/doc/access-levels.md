Access levels
========

Access levels allow to protect database records.

There are 6 access levels:

 - **System**: Allows to gives a user a permissions to access to all records within the system.
 - **Organization**: Allows to gives a user a permissions to access to all records within the organization, regardless of the business unit hierarchical level to which a record belongs or the user is assigned to.
 - **Division**: Allows to gives a user a permissions to access to records in all business units are assigned to the user and all business units subordinate to business units are assigned to the user.
 - **Business Unit**: Allows to gives a user a permissions to access to records in all business units are assigned to the user.
 - **User**: Allows to gives a user a permissions to access to own records and records that are shared with the user.
 - **None**: Access denied.

[Examples](./examples.md)
  
There are several ways to protect the records with access levels.

###Data grids protections.

All records in datagrids automatically protect with access levels. Developer doesn't need turn on the protection manually.

Now it protects view permission for records.

###Protection with Param Converters.

When developer use Sensio Param converter in actions, all the entities records protects with ACL access levels. Information about permission to protect was takes from action ACL annotation.

If Param converter ACL access level check can't protect the entity, then was turn on protection on class level from action ACL annotation.

###Manual protection of select queries.

Developers can protect select DQL in QueryBuilder or Query with oro_security.acl_helper service:

``` php
$repository = $this->getDoctrine()
   ->getRepository('AcmeDemoBundle:Product');
$queryBuilder = $repository->createQueryBuilder('p')
   ->where('p.price > :price')
   ->setParameter('price', '19.99')
   ->orderBy('p.price', 'ASC');
   
$query = $this->get('ro_security.acl_helper')->apply($queryBuilder, 'VIEW');   
```

As result, $query will be marked as ACL protected and it will automatically delete records that user does't have permission.

###Manual access check on object.

Developer can check access to the given entity record by using isGranted method of Security facade service:

``` php
$entity = $repository->findOneBy('id' => 10);

if (!$this->securityFacade->isGranted('VIEW', $entity)) {
    throw new AccessDeniedException('Access denided');
} else {
    // access is granted
}  
```