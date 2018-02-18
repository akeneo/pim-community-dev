<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Updater\Remover;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\FamilyVariantInterface;
use Pim\Component\Catalog\ProductAndProductModel\Query\CountEntityWithFamilyVariantInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Removes a family variant if there is no entity with family variants already associated with it.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantRemover implements RemoverInterface
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var CountEntityWithFamilyVariantInterface */
    private $counter;

    /**
     * @param ObjectManager                         $objectManager
     * @param EventDispatcherInterface              $eventDispatcher
     * @param CountEntityWithFamilyVariantInterface $counter
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        CountEntityWithFamilyVariantInterface $counter
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->counter = $counter;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($familyVariant, array $options = []): RemoverInterface
    {
        if (!$familyVariant instanceof FamilyVariantInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($familyVariant),
                FamilyVariantInterface::class
            );
        }

        if ($this->hasEntityWithFamilyVariant($familyVariant)) {
            throw new \LogicException(
                sprintf(
                    'Family variant "%s", could not be removed as it is used by some entities with family variants.',
                    $familyVariant->getCode()
                )
            );
        }

        $this->removeFamilyVariant($familyVariant, $options);

        return $this;
    }

    /**
     * @param $familyVariant
     *
     * @return bool
     */
    private function hasEntityWithFamilyVariant(FamilyVariantInterface $familyVariant): bool
    {
        return 0 !== $this->counter->belongingToFamilyVariant($familyVariant);
    }

    /**
     * @param FamilyVariantInterface $familyVariant
     * @param array                  $options
     */
    private function removeFamilyVariant(FamilyVariantInterface $familyVariant, array $options): void
    {
        $objectId = $familyVariant->getId();

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(
            StorageEvents::PRE_REMOVE,
            new RemoveEvent($familyVariant, $objectId, $options)
        );

        $this->objectManager->remove($familyVariant);
        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(
            StorageEvents::POST_REMOVE,
            new RemoveEvent($familyVariant, $objectId, $options)
        );
    }
}
