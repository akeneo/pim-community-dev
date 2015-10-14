<?php

namespace Pim\Bundle\UserBundle\EventSubscriber\Security;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Class LoginSubscriber
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoginSubscriber implements EventSubscriberInterface
{
    /** @var SaverInterface */
    protected $saver;

    /**
     * @param SaverInterface $saver
     */
    public function __construct(SaverInterface $saver)
    {
        $this->saver = $saver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'security.interactive_login' => 'onLogin'
        ];
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function onLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof UserInterface) {
            $user->setLastLogin(new \DateTime('now', new \DateTimeZone('UTC')))
                 ->setLoginCount($user->getLoginCount() + 1);

            $this->saver->save($user);
        }
    }
}
