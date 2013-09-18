<?php

namespace Oro\Bundle\UserBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\UserBundle\Acl\Manager as AclManager;
use Oro\Bundle\UserBundle\Entity\User;

class ChangePasswordSubscriber extends UserSubscriber
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SUBMIT => 'onSubmit',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        );
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
                    array(
                        'auto_initialize' => false,
                        'mapped' => false,
                    )
                )
            );
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var User $user */
        $user = $event->getForm()->getParent()->getData();
        $form = $event->getForm();
        $plainPassword = $form->get('plainPassword');

        if ($this->isCurrentUser($user)) {
            $user->setPlainPassword($plainPassword->getData());
        }
    }
}
