<?php

namespace Akeneo\UserManagement\Bundle\Form\Handler;

use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class GroupHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param RequestStack  $requestStack
     * @param ObjectManager $manager
     */
    public function __construct(FormInterface $form, RequestStack $requestStack, ObjectManager $manager)
    {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  GroupInterface $entity
     * @return bool           True on successfull processing, false otherwise
     */
    public function process(GroupInterface $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->getRequest()->getMethod(), ['POST', 'PUT'])) {
            $this->form->handleRequest($this->getRequest());

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
     * @param GroupInterface                            $entity
     * @param UserInterface[] $appendUsers
     * @param UserInterface[] $removeUsers
     */
    protected function onSuccess(GroupInterface $entity, array $appendUsers, array $removeUsers)
    {
        $this->appendUsers($entity, $appendUsers);
        $this->removeUsers($entity, $removeUsers);
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Append users to group
     *
     * @param GroupInterface                            $group
     * @param UserInterface[] $users
     */
    protected function appendUsers(GroupInterface $group, array $users)
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
     * @param GroupInterface                            $group
     * @param UserInterface[] $users
     */
    protected function removeUsers(GroupInterface $group, array $users)
    {
        /** @var $user UserInterface */
        foreach ($users as $user) {
            $user->removeGroup($group);
            $this->manager->persist($user);
        }
    }

    /**
     * Get Request
     *
     * @return null|Request
     */
    protected function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }
}
