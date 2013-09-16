<?php

namespace Oro\Bundle\UserBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\UserBundle\Acl\Manager as AclManager;
use Oro\Bundle\UserBundle\Entity\User;

class ChangePasswordSubscriber implements EventSubscriberInterface
{
    /**
     * @var AclManager
     */
    protected $aclManager;

    /**
     * @var SecurityContextInterface
     */
    protected $security;

    /**
     * @param AclManager               $aclManager ACL manager
     * @param SecurityContextInterface $security   Security context
     */
    public function __construct(
        AclManager $aclManager,
        SecurityContextInterface $security
    ) {
        $this->aclManager = $aclManager;
        $this->security   = $security;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SUBMIT => 'onSubmit',
        );
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var User $user */
        $user = $event->getForm()->getParent()->getData();
        $plainPassword = $event->getForm()->get('plainPassword');

        if ($this->isCurrentUser($user)) {
            $user->setPlainPassword($plainPassword->getData());
        }
    }

    /**
     * Returns true if passed user is currently authenticated
     *
     * @param  User $user
     * @return bool
     */
    protected function isCurrentUser(User $user)
    {
        $token = $this->security->getToken();
        $currentUser = $token ? $token->getUser() : null;

        if ($user->getId() && is_object($currentUser)) {
            return $currentUser->getId() == $user->getId();
        }

        return false;
    }
}
