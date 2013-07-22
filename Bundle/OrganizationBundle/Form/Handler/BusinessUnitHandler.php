<?php

namespace Oro\Bundle\OrganizationBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\TagBundle\Entity\TagManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\TagBundle\Form\Handler\TagHandlerInterface;

class BusinessUnitHandler implements TagHandlerInterface
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var TagManager
     */
    protected $tagManager;

    /**
     * @param FormInterface $form
     * @param Request       $request
     * @param ObjectManager $manager
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $manager)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  BusinessUnit $entity
     * @return bool  True on successfull processing, false otherwise
     */
    public function process(BusinessUnit $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $appendUsers = array();//$this->form->get('appendUsers')->getData();
                $removeUsers = array();//$this->form->get('removeUsers')->getData();
                $this->onSuccess($entity, $appendUsers, $removeUsers);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param BusinessUnit  $entity
     * @param User[] $appendUsers
     * @param User[] $removeUsers
     */
    protected function onSuccess(BusinessUnit $entity, array $appendUsers, array $removeUsers)
    {
        //$this->appendUsers($entity, $appendUsers);
        //$this->removeUsers($entity, $removeUsers);
        $this->manager->persist($entity);
        $this->manager->flush();
        $this->tagManager->saveTagging($entity);
    }

    /**
     * Append users to group
     *
     * @param BusinessUnit  $businessUnit
     * @param User[] $users
     */
    protected function appendUsers(BusinessUnit $businessUnit, array $users)
    {
        /** @var $user User */
        foreach ($users as $user) {
            $user->addBusinessUnit($businessUnit);
            $this->manager->persist($user);
        }
    }

    /**
     * Remove users from group
     *
     * @param BusinessUnit  $businessUnit
     * @param User[] $users
     */
    protected function removeUsers(BusinessUnit $businessUnit, array $users)
    {
        /** @var $user User */
        foreach ($users as $user) {
            $user->removeBusinessUnit($businessUnit);
            $this->manager->persist($user);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setTagManager(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }
}
