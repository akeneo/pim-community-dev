<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\EventListener;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\EnrichBundle\Doctrine\Persister\CompletenessPersister;
use Pim\Component\Catalog\Model\Completeness;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 *
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PersisterConfiguratorSubscriber implements EventSubscriberInterface
{
    /** @var bool */
    private $isAlreadyRegistered;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->isAlreadyRegistered = false;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_SAVE => 'registerCompletenessPersister',
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function registerCompletenessPersister(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        if (!$this->isAlreadyRegistered) {
            $uow = $this->entityManager->getUnitOfWork();
            $setPersister = \Closure::bind(function ($uow, $className, $persister) {
                $uow->persisters[$className] = $persister;
            }, null, $uow);
            $class = $this->entityManager->getClassMetadata(Completeness::class);
            $setPersister($uow, Completeness::class, new CompletenessPersister($this->entityManager, $class));
            $this->isAlreadyRegistered = true;
        }
    }
}
