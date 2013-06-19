User Bundle
===========
Provides user management functionality (authentication, authorization, etc).


Areas of Responsibilities
----------------------------------

- user management
- authentication
- authorization

ACL
===========

Access control list from User bundle allow to dynamically manipulate access to different part of project for user roles.

ACL Resource Definition
----------------------------------

``` php
Acl resource definition with class annotation @Acl.

<?php
...
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
...
use Oro\Bundle\UserBundle\Annotation\Acl;

/**
 * @Route("/acl")
 * @Acl(
 *      id = "acme_demo_test_controller",
 *      name="Test controller",
 *      description = "Test controller for ACL"
 * )
 */
class AclAccessController extends Controller
{
...
    /**
     * @Acl(
     *      id = "acme_demo_test_controller_role_manager",
     *      name="Action for ROLE_MANAGER",
     *      description = "Action for ROLE_MANAGER",
     *      parent = "acme_demo_test_controller"
     * )
     * @Route("/manager", name="acme_demo_acl_manager_only")
     */
    public function managerEnabledAction()
    {
        return  new Response('Action for ROLE_MANAGER');
    }
...
}
```

Acl annotation supports next parameters:

- id - ACL Resource id
- name - name of ACL Resource
- description
- parent - parent ACL Resorce. If ACL annotation doesn't have parent parameter, when parent set to "root" resource.

Acl resource definition with config files.
----------------------------------

In some cases we can't use annotations for ACL definition. To implement ACL resource, You can use definition in config file.

You must create acl.yml file in Resource/config directory of Your bundle. This file contain array with ACL resources definition.
Example:

```
test_acl:
    name: Test ACL name
    description: Some description for ACL resource
    parent: root
    class: Acme\SomeBundle\Controller\SomeController
    method: someAction
```

Acl resource Ancestor.
----------------------------------

In some cases we must have one ACL resource for different actions. For eample, add user action,
REST API add user action, SOAP API add user action. In such cases we can use @AclAncestor annotation to assign ACL to this methods.

As a parameter, @AclAncestor annotation takes id of ACL Resource to take access from.

Example:

``` php
// Controller with main ACL definition
<?php
...
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
...
use Oro\Bundle\UserBundle\Annotation\Acl;
...
class AclAccessController extends Controller
{
...
    /**
     * @Acl(
     *      id = "acme_demo_test_controller_role_manager",
     *      name="Action for ROLE_MANAGER",
     *      description = "Action for ROLE_MANAGER",
     *      parent = "acme_demo_test_controller"
     * )
...
     */
    public function managerEnabledAction()
    {
        return  new Response('Action for ROLE_MANAGER');
    }
...
}


// AclAncestor annotation in REST API action
<?php
...
use Symfony\Bundle\FrameworkBundle\Controller\Controller\Api;
...
use Oro\Bundle\UserBundle\Annotation\AclAncestor;
...
class RestAclAccessController extends Controller
{
...
    /**
     * @AclAncestor("acme_demo_test_controller_role_manager")
     */
    public function managerEnabledAction()
    {
        return  new Response('Action for ROLE_MANAGER');
    }
...
}
```

ACL Synchronization.
----------------------------------

After all annotations wat added to the methods, it must be run synchronization between databace and class annotations by command:

```
php app/console oro:acl:load
```

After Acl Resource process was complete, it start clear cache command. So if we need to cleat cache for example, in prod environment, we must use --env=prod parameter:

```
php app/console oro:acl:load --env=dev
```

Access check process
----------------------------------

All the actions checks for access permissions.

System search for ACL resource by @Acl annotation, @AclAncestor annotation or by definition in config files.
If Acl resource was found, system takes roles array witch have access to this action and compage it with user roles.

ACL check for logged users.
----------------------------------

If user have role with allowed access, action continue to process.

Otherwise, was throw access denied exception.

By default, system have user admin@example.com with password admin in fixtures. This user have permission to all the actions of project.


ACL check for non logged users.
----------------------------------

If user does not logged, system check permission for IS_AUTHENTICATED_ANONYMOUSLY role.

If action does not have permissions, it wheel be redirected to login page.

Actions without ACL resources are closed to access for non logged users.

By default, non logged users have permissions only to login page, restore password page and to logout page.

ACL Manager
----------------------------------

Access to acl functions implement by oro_user.acl.manager service.

Some useful functions:

- getAclRoles - return roles with access for ACL resource id
- isResourceGranted - return true if resource is granted for user
- isClassMethodGranted - return true if mehod is granted for user
- getAclForUser - get array with alowed resoure ids for user

Translation of ACL Resources
----------------------------------

To get translation messages of ACL Resources, we can use console command oro:acl:translation-update.

Example:

```
php app/console oro:acl:translation-update --dump-messages en OroUserBundle
```

This command will dump translation messages with ACL Resources names of User Bundle and description into console.

```
php app/console oro:acl:translation-update --flush en OroUserBundle
```

This command will save translation files with ACL Resources of User Bundle names and description into console.

