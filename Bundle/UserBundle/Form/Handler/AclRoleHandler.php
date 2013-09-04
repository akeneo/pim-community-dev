<?php
namespace Oro\Bundle\UserBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\UserBundle\Form\Type\ACLRoleType;

class AclRoleHandler
{
    /**
     * @var Request
     */
    protected $request;

    protected $formFactory;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var ObjectManager
     */
    protected $manager;

    protected $aclManager;

    public function __construct($formFactory, Request $request, ObjectManager $manager, $aclManager)
    {
        $this->request = $request;
        $this->manager = $manager;
        $this->aclManager = $aclManager;
    }

    public function createForm($entity)
    {
        /*$data = array(
            array(
                'oid' => 'entity:Oro\Bundle\EmailBundle\Entity\Email',
                'name' => 'Email',
                'permissions' => array (
                    'VIEW' => false,
                    'CREATE' => true,
                    'EDIT' => false
                )
            ),
            array(
                'oid' => 'entity:Oro\Bundle\EmailBundle\Entity\Email1',
                'name' => 'Email1',
                'permissions' => array (
                    'VIEW' => false,
                    'EDIT' => true,
                    'delete' => false
                )
            )
        );
        foreach($data as $formData) {
            $dataArray[] = array_merge(
                array('oid' => array(
                    'oid' => $formData['oid'],
                    'name' => $formData['name']
                )),
                $formData['permissions']
            );
        }*/

        $this->form =  $this->formFactory->create(new ACLRoleType($this->aclManager->getPrivilegeRepository()->getPermissionNames('entity')), $entity);
        $this->form->get('entities')->setData($this->aclManager->getPrivilegeRepository()->getPrivileges($this->aclManager->getSid($entity)));

        return $this->form;
    }

    public function process($entity)
    {
        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->manager->persist($entity);
                $this->manager->flush();
                $this->aclManagermanager->getPrivilegeRepository()->savePrivileges($this->aclManagermanager->getSid($entity), $this->form->get('entities'));

                return true;
            }
        }

        return false;
    }

    public function createView()
    {
        return $this->form->createView();
    }
}