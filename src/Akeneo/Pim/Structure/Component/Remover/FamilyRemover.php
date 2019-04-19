<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Remover;

use Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query\CountProductsWithFamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Removes a family if no product uses it and no family variant belong to it.
 *
 * @author    Julian Prud'homme <julian.prudhomme@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyRemover implements RemoverInterface
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var CountProductsWithFamilyInterface */
    private $counter;

    /**
     * @param ObjectManager                    $objectManager
     * @param EventDispatcherInterface         $eventDispatcher
     * @param CountProductsWithFamilyInterface $counter
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        CountProductsWithFamilyInterface $counter
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->counter = $counter;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($family, array $options = []): void
    {
        $this->ensureIsFamily($family);
        $this->ensureFamilyHasNoVariants($family);
        $this->ensureFamilyHasNoProducts($family);

        $this->sendEvent($family, StorageEvents::PRE_REMOVE);

        $this->objectManager->remove($family);
        $this->objectManager->flush();

        $this->sendEvent($family, StorageEvents::POST_REMOVE);
    }
    
    private function ensureIsFamily($family): void
    {
        if (! $family instanceof FamilyInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($family),
                FamilyInterface::class
            );
        }
    }

    /**
     * @param FamilyInterface $family
     *
     * @return void
     */
    private function ensureFamilyHasNoVariants(FamilyInterface $family): void
    {
        if (! $family->getFamilyVariants()->isEmpty()) {
            throw new \LogicException(sprintf(
                'Can not remove family "%s" because it is linked to family variants.',
                $family->getCode()
            ));
        }
    }

    /**
     * @param FamilyInterface $family
     *
     * @return void
     */
    private function ensureFamilyHasNoProducts(FamilyInterface $family): void
    {
        if ($this->counter->count($family) > 0) {
            throw new \LogicException(sprintf(
                'Family "%s" could not be removed as it still has products',
                $family->getCode()
            ));
        }
    }

    /**
     * @param FamilyInterface $family
     * @param string $event
     *
     * @return void
     */
    private function sendEvent(FamilyInterface $family, string $event): void
    {
        $this->eventDispatcher->dispatch(
            $event,
            new RemoveEvent($family, $family->getId(), ['unitary' => true])
        );
    }
}
