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
     */
    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
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
        $this->form = $this->formFactory->create(
            new ACLRoleType(
                array(
                    self::ENTITY_FIELD_NAME => $this->aclManager->getPrivilegeRepository()->getPermissionNames('entity'),
                    self::ACTION_FIELD_NAME => $this->aclManager->getPrivilegeRepository()->getPermissionNames('action')
                )
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
                $this->manager->persist($role);
                $this->manager->flush();

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
        foreach($privileges as $privilege) {
            foreach ($privilege->getPermissions() as $permission)
            {
                $permission->setAccessLevel((bool)$permission->getAccessLevel());
            }
        }
        $this->form->get(self::ACTION_FIELD_NAME)->setData($this->filterPrivileges($privileges, self::ACTION_FIELD_NAME));
        $this->form->get(self::ENTITY_FIELD_NAME)->setData($this->filterPrivileges($privileges, self::ENTITY_FIELD_NAME));
    }

    /**
     * @param Role $role
     */
    protected function processPrivileges(Role $role)
    {
        $entities = $this->form->get(self::ENTITY_FIELD_NAME)->getData();
        $this->fxPrivilegeValue($entities, 5);

        $actions = $this->form->get(self::ACTION_FIELD_NAME)->getData();
        $this->fxPrivilegeValue($actions, 1);


        $this->aclManager->getPrivilegeRepository()->savePrivileges(
            $this->aclManager->getSid($role),
            new ArrayCollection(
                array_merge(
                    $this->form->get(self::ENTITY_FIELD_NAME)->getData(),
                    $this->form->get(self::ACTION_FIELD_NAME)->getData()
                )
            )
        );
    }

    /**
     * @param ArrayCollection $privileges
     * @param $rootId
     * @return ArrayCollection
     */
    protected function filterPrivileges(ArrayCollection $privileges, $rootId)
    {
        return $privileges->filter(
            function($entry) use ($rootId){
                return ($entry->getRootId() == $rootId);
            }
        );
    }

    /**
     * @param ArrayCollection|array $privileges
     * @param $value
     */
    protected function fxPrivilegeValue($privileges, $value)
    {
        foreach($privileges as $privilege) {
            foreach ($privilege->getPermissions() as $permission)
            {
                $permission->setAccessLevel($permission->getAccessLevel() ? $value : 0);
            }
        }
    }
}
