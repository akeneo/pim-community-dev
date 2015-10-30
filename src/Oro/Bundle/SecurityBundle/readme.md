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

- [Implementation](./Resources/doc/implementation.md)
- [UI](./Resources/doc/ui.md)
- [ACL manager](./Resources/doc/acl-manager.md)
- [Access levels] (./Resources/doc/access-levels.md)
- [Examples](./Resources/doc/examples.md)

