<?php
namespace Oro\Bundle\UserBundle\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Form\Type\AclRoleType;
use Oro\Bundle\UserBundle\Entity\Role;

use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;

class AclRoleHandler
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var AclManager
     */
    protected $aclManager;

    /**
     * @var array
     */
    protected $privilegeConfig;

    /**
     * @param FormFactory $formFactory
     * @param array $privilegeConfig
     */
    public function __construct(FormFactory $formFactory, array $privilegeConfig)
    {
        $this->formFactory = $formFactory;
        $this->privilegeConfig = $privilegeConfig;
    }

    /**
     * @param AclManager $aclManager
     */
    public function setAclManager(AclManager $aclManager)
    {
        $this->aclManager = $aclManager;
    }

    /**
     * @param ObjectManager $manager
     */
    public function setEntityManager(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Create form for role manipulation
     *
     * @param Role $role
     * @return FormInterface
     */
    public function createForm(Role $role)
    {
        foreach ($this->privilegeConfig as $configName => $config) {
            $this->privilegeConfig[$configName]['permissions'] = $this->aclManager
                ->getPrivilegeRepository()->getPermissionNames($config['types']);
        }

        $this->form = $this->formFactory->create(
            new ACLRoleType(
                $this->privilegeConfig
            ),
            $role
        );

        return $this->form;
    }

    /**
     * Save role
     *
     * @param Role $role
     * @return bool
     */
    public function process(Role $role)
    {
        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $appendUsers = $this->form->get('appendUsers')->getData();
                $removeUsers = $this->form->get('removeUsers')->getData();
                $this->onSuccess($role, $appendUsers, $removeUsers);

                $this->processPrivileges($role);

                return true;
            }
        } else {
            $this->setRolePrivileges($role);
        }

        return false;
    }

    /**
     * Create form view for current form
     *
     * @return \Symfony\Component\Form\FormView
     */
    public function createView()
    {
        return $this->form->createView();
    }

    /**
     * @param Role $role
     */
    protected function setRolePrivileges(Role $role)
    {
        /** @var ArrayCollection $privileges */
        $privileges = $this->aclManager->getPrivilegeRepository()->getPrivileges($this->aclManager->getSid($role));

        foreach ($this->privilegeConfig as $fieldName => $config) {
            $sortedPrivileges = $this->filterPrivileges($privileges, $config['types']);
            if ($config['fix_values'] || !$config['show_default']) {
                foreach ($sortedPrivileges as $sortedPrivilege) {
                    if (!$config['show_default']
                        && $sortedPrivilege->getIdentity()->getName() == AclPrivilegeRepository::ROOT_PRIVILEGE_NAME) {
                        $sortedPrivileges->removeElement($sortedPrivilege);
                        continue;
                    }
                    if ($config['fix_values']) {
                        foreach ($sortedPrivilege->getPermissions() as $permission) {
                            $permission->setAccessLevel((bool)$permission->getAccessLevel());
                        }
                    }
                }
            }

            $this->form->get($fieldName)->setData($sortedPrivileges);
        }
    }

    /**
     * @param Role $role
     */
    protected function processPrivileges(Role $role)
    {
        $formPrivileges = array();
        foreach ($this->privilegeConfig as $fieldName => $config) {
            $privileges = $this->form->get($fieldName)->getData();
            if ($config['fix_values']) {
                $this->fxPrivilegeValue($privileges, $config['default_value']);
            }
            $formPrivileges = array_merge($formPrivileges, $privileges);
        }

        $this->aclManager->getPrivilegeRepository()->savePrivileges(
            $this->aclManager->getSid($role),
            new ArrayCollection($formPrivileges)
        );
    }

    /**
     * @param ArrayCollection $privileges
     * @param array $rootIds
     * @return ArrayCollection
     */
    protected function filterPrivileges(ArrayCollection $privileges, array $rootIds)
    {
        return $privileges->filter(
            function ($entry) use ($rootIds) {
                return in_array($entry->getExtensionKey(), $rootIds);
            }
        );
    }

    /**
     * @param ArrayCollection|array $privileges
     * @param $value
     */
    protected function fxPrivilegeValue($privileges, $value)
    {
        foreach ($privileges as $privilege) {
            foreach ($privilege->getPermissions() as $permission) {
                $permission->setAccessLevel($permission->getAccessLevel() ? $value : 0);
            }
        }
    }

    /**
     * "Success" form handler
     *
     * @param Role   $entity
     * @param User[] $appendUsers
     * @param User[] $removeUsers
     */
    protected function onSuccess(Role $entity, array $appendUsers, array $removeUsers)
    {
        $this->appendUsers($entity, $appendUsers);
        $this->removeUsers($entity, $removeUsers);
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Append users to role
     *
     * @param Role   $role
     * @param User[] $users
     */
    protected function appendUsers(Role $role, array $users)
    {
        /** @var $user User */
        foreach ($users as $user) {
            $user->addRole($role);
            $this->manager->persist($user);
        }
    }

    /**
     * Remove users from role
     *
     * @param Role   $role
     * @param User[] $users
     */
    protected function removeUsers(Role $role, array $users)
    {
        /** @var $user User */
        foreach ($users as $user) {
            $user->removeRole($role);
            $this->manager->persist($user);
        }
    }
}
