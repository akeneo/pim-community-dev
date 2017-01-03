<?php

namespace Oro\Bundle\UserBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupHandler
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
     * @param FormInterface $form
     * @param Request       $request
     * @param ObjectManager $manager
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $manager)
    {
        $this->form = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  Group $entity
     * @return bool  True on successfull processing, false otherwise
     */
    public function process(Group $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $appendUsers = $this->form->get('appendUsers')->getData();
                $removeUsers = $this->form->get('removeUsers')->getData();
                $this->onSuccess($entity, $appendUsers, $removeUsers);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param Group           $entity
     * @param UserInterface[] $appendUsers
     * @param UserInterface[] $removeUsers
     */
    protected function onSuccess(Group $entity, array $appendUsers, array $removeUsers)
    {
        $this->appendUsers($entity, $appendUsers);
        $this->removeUsers($entity, $removeUsers);
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Append users to group
     *
     * @param Group           $group
     * @param UserInterface[] $users
     */
    protected function appendUsers(Group $group, array $users)
    {
        /** @var $user UserInterface */
        foreach ($users as $user) {
            $user->addGroup($group);
            $this->manager->persist($user);
        }
    }

    /**
     * Remove users from group
     *
     * @param Group          $group
     * @param UserInterface[] $users
     */
    protected function removeUsers(Group $group, array $users)
    {
        /** @var $user UserInterface */
        foreach ($users as $user) {
            $user->removeGroup($group);
            $this->manager->persist($user);
        }
    }
}
