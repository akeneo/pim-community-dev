<?php

namespace Oro\Bundle\UserBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Pim\Bundle\UserBundle\Entity\UserInterface;

class UserSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;


    /**
     * @param FormFactoryInterface  $factory      Factory to add new form children
     * @param TokenStorageInterface $tokenStorage Token storage
     */
    public function __construct(
        FormFactoryInterface $factory,
        TokenStorageInterface $tokenStorage
    ) {
        $this->factory      = $factory;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
        );
    }

    public function preSetData(FormEvent $event)
    {
        /* @var $entity UserInterface */
        $entity = $event->getData();
        $form   = $event->getForm();

        if (is_null($entity)) {
            return;
        }

        if ($entity->getId()) {
            $form->remove('plainPassword');
        }

        // do not allow user to disable his own account
        $form->add(
            $this->factory->createNamed(
                'enabled',
                'choice',
                $entity->getId() ? $entity->isEnabled() : '',
                array(
                    'label'           => 'Status',
                    'required'        => true,
                    'disabled'        => $this->isCurrentUser($entity),
                    'choices'         => array('Inactive', 'Active'),
                    'empty_value'     => 'Please select',
                    'empty_data'      => '',
                    'auto_initialize' => false
                )
            )
        );

        if (!$this->isCurrentUser($entity)) {
            $form->remove('change_password');
        }
    }

    /**
     * Returns true if passed user is currently authenticated
     *
     * @param  UserInterface $user
     * @return bool
     */
    protected function isCurrentUser(UserInterface $user)
    {
        $token = $this->tokenStorage->getToken();
        $currentUser = $token ? $token->getUser() : null;
        if ($user->getId() && is_object($currentUser)) {
            return $currentUser->getId() == $user->getId();
        }

        return false;
    }
}
