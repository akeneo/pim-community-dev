<?php

namespace Akeneo\UserManagement\Bundle\Form\Handler;

use Akeneo\UserManagement\Bundle\Form\Type\AclRoleType;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Domain\Permissions\MinimumEditRolePermission;
use Akeneo\UserManagement\Domain\Permissions\Query\EditRolePermissionsRoleQuery;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Extension\ActionAclExtension;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Overriden AclRoleHandler to remove deactivated locales from the acl role form
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AclRoleHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var ObjectManager */
    protected $manager;

    /** @var AclManager */
    protected $aclManager;

    public function __construct(
        private readonly FormFactory $formFactory,
        private array $privilegeConfig,
        private readonly RequestStack $requestStack,
        private readonly EditRolePermissionsRoleQuery $editRolePermissionsRoleQuery,
        private readonly TranslatorInterface $translator,
        private readonly ActionAclExtension $aclExtension,
    ) {
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
     * Create form for role manipulation
     *
     * @param Role $role
     *
     * @return FormInterface
     */
    public function createForm(Role $role)
    {
        foreach ($this->privilegeConfig as $configName => $config) {
            $this->privilegeConfig[$configName]['permissions'] = $this->aclManager
                ->getPrivilegeRepository()->getPermissionNames($config['types']);
        }

        $this->form = $this->formFactory->create(
            AclRoleType::class,
            $role,
            [
                'privilegeConfigOption' => $this->privilegeConfig,
                'constraints' => new Callback([$this, 'validateEditRolePermissions']),
            ]
        );

        return $this->form;
    }

    public function validateEditRolePermissions(Role $role, ExecutionContextInterface $context): void
    {
        /** @var array<AclPrivilege> $formPrivileges */
        $formPrivileges = [];
        foreach ($this->privilegeConfig as $fieldName => $config) {
            $privileges = $this->form->get($fieldName)->getData();
            $formPrivileges = array_merge($formPrivileges, $privileges);
        }

        if ($this->editRolePermissionsRoleQuery->isLastRoleWithEditRolePermissions($role)) {
            // This function extract the values from the form inputs by the user
            $filterSelectedEditRolePrivilegesFn = function (AclPrivilege $formPrivilege) {
                // Keep only the privileges with the identity/key for the minimum edit role permissions
                if (false === in_array(
                    $formPrivilege->getIdentity()->getId(),
                    MinimumEditRolePermission::getAllValues()
                )) {
                    return false;
                }
                // With the remaining privileges, we identify if it has been checked by getting the 'EXECUTE' with SYSTEM_LEVEL access (NONE_LEVEL if unchecked)
                // for example :
                //  [
                //      'identity' => ['id' => 'action:oro_config_system'],
                //      'permissions' => ['elements' => ['EXECUTE' => [
                //                  'name' => 'EXECUTE',
                //                  'accessLevel' => 5,
                //              ],
                //      ]],
                //  ]
                return array_filter(
                    $formPrivilege->getPermissions()->toArray(),
                    fn ($permission) => $permission->getName() === $this->aclExtension->getDefaultPermission() && $permission->getAccessLevel() === AccessLevel::SYSTEM_LEVEL
                );
            };

            $editRoleActivePrivileges = array_filter($formPrivileges, $filterSelectedEditRolePrivilegesFn);
            if (count($editRoleActivePrivileges) < count(MinimumEditRolePermission::getAllValues())) {
                $context
                    ->buildViolation($this->translator->trans('pim_user.controller.role.message.cannot_remove_last_edit_role_permission'))
                    ->addViolation();
            }
        }
    }

    /**
     * Save role
     *
     * @param Role $role
     *
     * @return bool
     */
    public function process(Role $role)
    {
        if (in_array($this->getRequest()->getMethod(), ['POST', 'PUT'])) {
            $this->form->handleRequest($this->getRequest());

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

    public function reinitializeData(Role $role)
    {
        $errors = $this->form?->getErrors();
        if ($this->form->isSubmitted() && $errors) {
            $this->createForm($role);
            $this->setRolePrivileges($role);
            foreach ($errors as $error) {
                $this->form->addError($error);
            }
        }
    }

    /**
     * Create form view for current form
     *
     * @return FormView
     */
    public function createView()
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
    protected function setRolePrivileges(Role $role)
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
    protected function processPrivileges(Role $role)
    {
        $formPrivileges = [];
        foreach ($this->privilegeConfig as $fieldName => $config) {
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
     *
     * @return ArrayCollection
     */
    protected function filterPrivileges(ArrayCollection $privileges, array $rootIds)
    {
        return $privileges->filter(
            function (AclPrivilege $entry) use ($rootIds) {
                return in_array($entry->getExtensionKey(), $rootIds) && $entry->isVisible();
            }
        );
    }

    /**
     * "Success" form handler
     *
     * @param Role            $entity
     * @param UserInterface[] $appendUsers
     * @param UserInterface[] $removeUsers
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
     * @param Role            $role
     * @param UserInterface[] $users
     */
    protected function appendUsers(Role $role, array $users)
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
    protected function removeUsers(Role $role, array $users)
    {
        /** @var $user UserInterface */
        foreach ($users as $user) {
            $user->removeRole($role);
            $this->manager->persist($user);
        }
    }
}
