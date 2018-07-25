<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\EventSubscriber;

use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvent;
use Akeneo\Tool\Bundle\VersioningBundle\Event\BuildVersionEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Add current user
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddUserSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface         $tokenStorage
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage = null
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            BuildVersionEvents::PRE_BUILD => 'preBuild',
        ];
    }

    /**
     * @param BuildVersionEvent $event
     *
     * @return BuildVersionEvent
     */
    public function preBuild(BuildVersionEvent $event)
    {
        if (null === $this->tokenStorage) {
            return $event;
        }

        $token = $this->tokenStorage->getToken();
        if (null !== $token && $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $event->setUsername($token->getUser()->getUsername());
        }

        return $event;
    }
}
