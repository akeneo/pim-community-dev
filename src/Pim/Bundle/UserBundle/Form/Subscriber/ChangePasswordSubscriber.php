<?php

namespace Pim\Bundle\UserBundle\Form\Subscriber;

use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ChangePasswordSubscriber
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangePasswordSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

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
            $form->add('currentPassword', 'password', [
                'auto_initialize' => false,
                'mapped'          => false,
            ]);
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

    /**
     * Returns true if passed user is currently authenticated
     *
     * @param UserInterface $user
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
