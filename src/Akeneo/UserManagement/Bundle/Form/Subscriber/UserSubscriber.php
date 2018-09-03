<?php

namespace Akeneo\UserManagement\Bundle\Form\Subscriber;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
        $this->factory = $factory;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }

    public function preSetData(FormEvent $event)
    {
        /* @var $entity UserInterface */
        $entity = $event->getData();
        $form = $event->getForm();

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
                ChoiceType::class,
                $entity->getId() ? $entity->isEnabled() : '',
                [
                    'label'           => 'pim_user.user.fields.status',
                    'required'        => true,
                    'disabled'        => $this->isCurrentUser($entity),
                    'choices'         => ['Inactive' => 0, 'Active' => 1],
                    'placeholder'     => 'Please select',
                    'empty_data'      => '',
                    'auto_initialize' => false
                ]
            )
        );

        if (!$this->isCurrentUser($entity)) {
            $form->remove('change_password');
        }
    }

    /**
     * Returns true if passed user is currently authenticated
     *
     * @param  \Akeneo\UserManagement\Component\Model\UserInterface $user
     *
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
