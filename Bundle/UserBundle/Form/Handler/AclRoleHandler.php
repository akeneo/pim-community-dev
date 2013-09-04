<?php
namespace Oro\Bundle\UserBundle\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\UserBundle\Form\Type\AclRoleType;
use Oro\Bundle\UserBundle\Entity\Role;

use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

/**
 * @todo: delete this uses
 */
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;

class AclRoleHandler
{
    const ENTITY_FIELD_NAME = 'entity';
    const ACTION_FIELD_NAME = 'action';

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
     * @param FormFactory $formFactory
     * @param Request $request
     * @param ObjectManager $manager
     * @param AclManager $aclManager
     */
    public function __construct(FormFactory $formFactory, Request $request, ObjectManager $manager, AclManager $aclManager)
    {
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->manager = $manager;
        $this->aclManager = $aclManager;
    }

    /**
     * Create form for role manipulation
     *
     * @param Role $role
     * @return FormInterface
     */
    public function createForm(Role $role)
    {
        $this->form = $this->formFactory->create(
            new ACLRoleType(
                array(
                    self::ENTITY_FIELD_NAME => $this->aclManager->getPrivilegeRepository()->getPermissionNames('entity'),
                    self::ACTION_FIELD_NAME => $this->aclManager->getPrivilegeRepository()->getPermissionNames('action')
                )
            ),
            $role
        );

        /**
         * @todo: Change priveleges to get them from the manager
         */
        $privileges = new ArrayCollection();
        for ($i = 0; $i < 10; $i++) {
            $privilege = new AclPrivilege();
            $privilege->setIdentity(new AclPrivilegeIdentity('entity OroSomeBundle:Entity' . $i, 'Entity Title ' . $i));

            foreach ($this->aclManager->getPrivilegeRepository()->getPermissionNames('entity') as $permission) {
                if (rand(0, 1)) {
                    $privilege->getPermissions()->add(new AclPermission($permission, (bool)rand(0, 1)));
                }
            }

            $privileges->add($privilege);
        }

        // $privileges = $this->aclManager->getPrivilegeRepository()->getPrivileges($this->aclManager->getSid($role));

        $this->form->get(self::ENTITY_FIELD_NAME)->setData($privileges);

        $privileges = new ArrayCollection();
        for ($i = 0; $i < 5; $i++) {
            $privilege = new AclPrivilege();
            $privilege->setIdentity(new AclPrivilegeIdentity('action OroSomeBundle:SomeCOntroller:SomeAction' . $i, 'Action Title ' . $i));

            foreach ($this->aclManager->getPrivilegeRepository()->getPermissionNames('action') as $permission) {
                if (rand(0, 1)) {
                    $privilege->getPermissions()->add(new AclPermission($permission, (bool)rand(0, 1)));
                }
            }

            $privileges->add($privilege);
        }

        $this->form->get(self::ACTION_FIELD_NAME)->setData($privileges);

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
                $this->manager->persist($role);
                $this->manager->flush();

                /**
                 * @todo: uncomment fo save data
                 */
                /*$this->aclManager->getPrivilegeRepository()->savePrivileges(
                    $this->aclManager->getSid($role),
                    new ArrayCollection(
                        array_merge(
                            $this->form->get(self::ENTITY_FIELD_NAME)->getData()->toArray(),
                            $this->form->get(self::ACTION_FIELD_NAME)->getData()->toArray()
                        )
                    )
                );*/

                return true;
            }
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
}
