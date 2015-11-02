<?php

namespace Oro\Bundle\UserBundle\Form\EventListener;

use Oro\Bundle\UserBundle\Acl\Manager as AclManager;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ChangePasswordSubscriber extends UserSubscriber
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT  => 'onSubmit',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        ];
    }

    /**
     * Re-create current password field in case of user don't filled any password field
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $isEmptyPassword = $data['currentPassword'] . $data['plainPassword']['first'];
        $isEmptyPassword = empty($isEmptyPassword);

        if ($isEmptyPassword) {
            $form->remove('currentPassword');

            $form->add(
                $this->factory->createNamed(
                    'currentPassword',
                    'password',
                    null,
                    [
                        'auto_initialize' => false,
                        'mapped'          => false,
                    ]
                )
            );
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var UserInterface $user */
        $user = $form->getParent()->getData();
        $plainPassword = $form->get('plainPassword');

        if ($this->isCurrentUser($user)) {
            $user->setPlainPassword($plainPassword->getData());
        }
    }
}
