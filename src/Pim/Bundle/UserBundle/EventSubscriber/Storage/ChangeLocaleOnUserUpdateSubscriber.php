<?php

namespace Pim\Bundle\UserBundle\EventSubscriber\Storage;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ChangeLocaleOnUserUpdateSubscriber
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChangeLocaleOnUserUpdateSubscriber implements EventSubscriberInterface
{
    /** @var UserContext */
    protected $userContext;

    /** @var RequestStack */
    protected $requestStack;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param UserContext         $userContext
     * @param RequestStack        $requestStack
     * @param TranslatorInterface $translator
     */
    public function __construct(UserContext $userContext, RequestStack $requestStack, TranslatorInterface $translator)
    {
        $this->userContext  = $userContext;
        $this->requestStack = $requestStack;
        $this->translator   = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE => 'setLocale',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function setLocale(GenericEvent $event)
    {
        $user = $event->getSubject();

        if (!$user instanceof UserInterface) {
            return;
        }

        if ($user !== $this->userContext->getUser()) {
            return;
        }

        $this->requestStack->getMasterRequest()->getSession()->set('_locale', $user->getUiLocale()->getLanguage());
        $this->translator->setLocale($user->getUiLocale()->getLanguage());
    }
}
