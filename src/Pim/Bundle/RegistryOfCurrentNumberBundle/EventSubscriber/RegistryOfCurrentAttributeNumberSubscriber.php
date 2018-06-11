<?php

declare(strict_types=1);

namespace Pim\Bundle\RegistryOfCurrentNumberBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\InstallerBundle\Event\InstallerEvents;
use Pim\Bundle\RegistryOfCurrentNumberBundle\Manager\RegistryOfCurrentNumberManager;
use Pim\Bundle\RegistryOfCurrentNumberBundle\Model\RegistryOfCurrentNumberInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Increment RegistryOfCurrentNumbers on entity save
 *
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegistryOfCurrentAttributeNumberSubscriber implements EventSubscriberInterface
{
    /** @var RegistryOfCurrentNumberManager */
    private $registryOfCurrentNumberManager;

    /**
     * @param RegistryOfCurrentNumberManager     $registryOfCurrentNumberManager
     */
    public function __construct(
        RegistryOfCurrentNumberManager $registryOfCurrentNumberManager
    ) {
        $this->registryOfCurrentNumberManager = $registryOfCurrentNumberManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'incrementRegistryOfCurrentNumbersManager',
            StorageEvents::POST_SAVE_ALL => 'countAllRegistryOfCurrentNumbers',
            StorageEvents::POST_REMOVE => 'decrementRegistryOfCurrentNumbersManager',
        ];
    }

    /**
     * @param GenericEvent $event
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function incrementRegistryOfCurrentNumbersManager(GenericEvent $event) : void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof Attribute) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if ($event->hasArgument('isScheduledForInsert') &&
            true == $event->getArgument('isScheduledForInsert')) {
            $this->registryOfCurrentNumberManager->increment();
        }
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function countAllRegistryOfCurrentNumbers(GenericEvent $event) : void
    {
        $subject = $event->getSubject();

        if (is_array($subject)) {
            if (current($subject) instanceof Attribute) {
                $this->registryOfCurrentNumberManager->countAllRegistryOfCurrentNumbers();
            }
        }
        return;
    }

    /**
     * @param GenericEvent $event
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function decrementRegistryOfCurrentNumbersManager(GenericEvent $event) : void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof Attribute) {
            return;
        }

        $this->registryOfCurrentNumberManager->decrement();
    }
}
