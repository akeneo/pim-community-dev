<?php

namespace Oro\Bundle\UserBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * TODO: Remove this class after api for acl is ready
 */
class RoleHandler
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
     * @param  Role $entity
     * @return bool True on successfull processing, false otherwise
     */
    public function process(Role $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $appendUsers = $this->form->get('appendUsers')->getData();
                $removeUsers = $this->form->get('removeUsers')->getData();
                $entity->setRole(strtoupper(trim(preg_replace('/[^\w\-]/i', '_', $entity->getLabel()))));
                $this->onSuccess($entity, $appendUsers, $removeUsers);

                return true;
            }
        }

        return false;
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
     * @param Role           $role
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
