<?php

namespace Pim\Bundle\UserBundle\EventSubscriber\Storage;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * Class SetPasswordFromPlainPasswordSubscriber
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SetPasswordFromPlainPasswordSubscriber implements EventSubscriberInterface
{
    /** @var EncoderFactoryInterface */
    protected $encoderFactory;

    /**
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE => 'setPassword',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function setPassword(GenericEvent $event)
    {
        $user = $event->getSubject();

        if (!$user instanceof UserInterface) {
            return;
        }

        if (0 === strlen($password = $user->getPlainPassword())) {
            return;
        }

        $encoder = $this->encoderFactory->getEncoder($user);

        $user->setPassword($encoder->encodePassword($password, $user->getSalt()));
        $user->eraseCredentials();
    }
}
