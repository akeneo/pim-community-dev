<?php

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Family saver, contains custom logic for family's product saving
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param ObjectManager                  $objectManager
     *                                                            {@see \Akeneo\Pim\Enrichment\Bundle\EventSubscriber\ComputeCompletenessOnFamilyUpdateSubscriber})
     * @param EventDispatcherInterface       $eventDispatcher
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function save($family, array $options = [])
    {
        $this->validateFamily($family);

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(new GenericEvent($family, $options), StorageEvents::PRE_SAVE);

        $this->objectManager->persist($family);

        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(new GenericEvent($family, $options), StorageEvents::POST_SAVE);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $families, array $options = [])
    {
        if (empty($families)) {
            return;
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(new GenericEvent($families, $options), StorageEvents::PRE_SAVE_ALL);

        foreach ($families as $family) {
            $this->validateFamily($family);

            $this->eventDispatcher->dispatch(new GenericEvent($family, $options), StorageEvents::PRE_SAVE);

            $this->objectManager->persist($family);
        }

        $this->objectManager->flush();

        foreach ($families as $family) {
            $this->eventDispatcher->dispatch(new GenericEvent($family, $options), StorageEvents::POST_SAVE);
        }

        $this->eventDispatcher->dispatch(new GenericEvent($families, $options), StorageEvents::POST_SAVE_ALL);
    }

    protected function validateFamily($family)
    {
        if (!$family instanceof FamilyInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Akeneo\Pim\Structure\Component\Model\FamilyInterface", "%s" provided.',
                    ClassUtils::getClass($family)
                )
            );
        }
    }
}
