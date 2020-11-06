<?php

namespace Akeneo\UserManagement\Bundle\Form\Handler;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Akeneo\UserManagement\Bundle\Form\Type\AclRoleType;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Overriden AclRoleHandler to remove deactivated locales from the acl role form
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AclRoleHandler
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var FormFactory */
    protected $formFactory;

    /** @var FormInterface */
    protected $form;

    /** @var ObjectManager */
    protected $manager;

    /** @var AclManager */
    protected $aclManager;

    /** @var array */
    protected $privilegeConfig;

    /**
     * @param FormFactory  $formFactory
     * @param array        $privilegeConfig
     * @param RequestStack $requestStack
     */
    public function __construct(FormFactoryInterface $formFactory, array $privilegeConfig, RequestStack $requestStack)
    {
        $this->formFactory = $formFactory;
        $this->privilegeConfig = $privilegeConfig;
        $this->requestStack = $requestStack;
    }

    /**
     * @param AclManager $aclManager
     */
    public function setAclManager(AclManager $aclManager): void
    {
        $this->aclManager = $aclManager;
    }

    /**
     * @param ObjectManager $manager
     */
    public function setEntityManager(ObjectManager $manager): void
    {
        $this->manager = $manager;
    }

    /**
     * Create form for role manipulation
     *
     * @param Role $role
     */
    public function createForm(Role $role): \Symfony\Component\Form\FormInterface
    {
        foreach ($this->privilegeConfig as $configName => $config) {
            $this->privilegeConfig[$configName]['permissions'] = $this->aclManager
                ->getPrivilegeRepository()->getPermissionNames($config['types']);
        }

        $this->form = $this->formFactory->create(
            AclRoleType::class,
            $role,
            ['privilegeConfigOption' => $this->privilegeConfig]
        );

        return $this->form;
    }

    /**
     * Save role
     *
     * @param Role $role
     */
    public function process(Role $role): bool
    {
        if (in_array($this->getRequest()->getMethod(), ['POST', 'PUT'])) {
            $this->form->handleRequest($this->getRequest());

            if ($this->form->isValid()) {
                $appendUsers = $this->form->get('appendUsers')->getData();
                $removeUsers = $this->form->get('removeUsers')->getData();

                if (empty($role->getRole())) {
                    $role->setRole(strtoupper(trim(preg_replace('/[^\w\-]/i', '_', $role->getLabel()))));
                }

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
     */
    public function createView(): FormView
    {
        return $this->form->createView();
    }

    /**
     * @return null|Request
     */
    protected function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @param Role $role
     */
    protected function setRolePrivileges(Role $role): void
    {
        /** @var ArrayCollection $privileges */
        $privileges = $this->aclManager->getPrivilegeRepository()->getPrivileges($this->aclManager->getSid($role));

        foreach ($this->privilegeConfig as $fieldName => $config) {
            $sortedPrivileges = $this->filterPrivileges($privileges, $config['types']);
            if (!$config['show_default']) {
                foreach ($sortedPrivileges as $sortedPrivilege) {
                    if ($sortedPrivilege->getIdentity()->getName() == AclPrivilegeRepository::ROOT_PRIVILEGE_NAME) {
                        $sortedPrivileges->removeElement($sortedPrivilege);
                        continue;
                    }
                }
            }

            $this->form->get($fieldName)->setData($sortedPrivileges);
        }
    }

    /**
     * @param Role $role
     */
    protected function processPrivileges(Role $role): void
    {
        $formPrivileges = [];
        foreach (array_keys($this->privilegeConfig) as $fieldName) {
            $privileges = $this->form->get($fieldName)->getData();
            $formPrivileges = array_merge($formPrivileges, $privileges);
        }

        $this->aclManager->getPrivilegeRepository()->savePrivileges(
            $this->aclManager->getSid($role),
            new ArrayCollection($formPrivileges)
        );
    }

    /**
     * @param ArrayCollection $privileges
     * @param array           $rootIds
     */
    protected function filterPrivileges(ArrayCollection $privileges, array $rootIds): \Doctrine\Common\Collections\ArrayCollection
    {
        return $privileges->filter(
            fn($entry) => in_array($entry->getExtensionKey(), $rootIds)
        );
    }

    /**
     * "Success" form handler
     *
     * @param Role            $entity
     * @param UserInterface[] $appendUsers
     * @param UserInterface[] $removeUsers
     */
    protected function onSuccess(Role $entity, array $appendUsers, array $removeUsers): void
    {
        $this->appendUsers($entity, $appendUsers);
        $this->removeUsers($entity, $removeUsers);
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Append users to role
     *
     * @param Role            $role
     * @param UserInterface[] $users
     */
    protected function appendUsers(Role $role, array $users): void
    {
        /** @var $user UserInterface */
        foreach ($users as $user) {
            $user->addRole($role);
            $this->manager->persist($user);
        }
    }

    /**
     * Remove users from role
     *
     * @param Role            $role
     * @param UserInterface[] $users
     */
    protected function removeUsers(Role $role, array $users): void
    {
        /** @var $user UserInterface */
        foreach ($users as $user) {
            $user->removeRole($role);
            $this->manager->persist($user);
        }
    }
}
