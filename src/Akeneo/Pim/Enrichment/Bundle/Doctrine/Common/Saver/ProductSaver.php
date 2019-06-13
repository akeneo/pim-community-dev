<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Manager\CompletenessManager;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\FamilyAddedToProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\FamilyOfProductChanged;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ParentOfProductAdded;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ParentOfProductChanged;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ValueAdded;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ValueDeleted;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ValueEdited;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Product saver, define custom logic and options for product saving
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ProductUniqueDataSynchronizer */
    protected $uniqueDataSynchronizer;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param ObjectManager $objectManager
     * @param CompletenessManager $completenessManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param ProductUniqueDataSynchronizer $uniqueDataSynchronizer
     */
    public function __construct(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        EventDispatcherInterface $eventDispatcher,
        ProductUniqueDataSynchronizer $uniqueDataSynchronizer,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->objectManager = $objectManager;
        $this->completenessManager = $completenessManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->uniqueDataSynchronizer = $uniqueDataSynchronizer;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function save($product, array $options = [])
    {
        $this->validateProduct($product);

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($product, $options));
        $productEvents = $product->popEvents();

        if ($this->doesProductNeedCompletenessCalculation($productEvents, $product->getFamily())) {
            $this->completenessManager->generateMissingForProduct($product);
        }
        if ($this->doesProductNeedUniqueDataSynchro($productEvents)) {
            $this->uniqueDataSynchronizer->synchronize($product);
        }

        $this->objectManager->persist($product);
        $this->objectManager->flush();

        if ($this->doesProductNeedReindexing($productEvents)) {
            $options['products_to_index'][$product->getIdentifier()] = true;
        }
        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($product, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $products, array $options = [])
    {
        if (empty($products)) {
            return;
        }

        $products = array_unique($products, SORT_REGULAR);

        foreach ($products as $product) {
            $this->validateProduct($product);
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($products, $options));

        $productsToIndex = [];

        foreach ($products as $product) {
            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($product, $options));

            $productEvents = $product->popEvents();

            if ($this->doesProductNeedCompletenessCalculation($productEvents)) {
                $this->completenessManager->generateMissingForProduct($product);
            }
            if ($this->doesProductNeedUniqueDataSynchro($productEvents)) {
                $this->uniqueDataSynchronizer->synchronize($product);
            }
            if ($this->doesProductNeedReindexing($productEvents)) {
                $options['products_to_index'][$product->getIdentifier()];
            }

            $this->objectManager->persist($product);
        }

        $this->objectManager->flush();

        foreach ($products as $product) {
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($product, $options));
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($products, $options));
    }

    protected function validateProduct($product)
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a %s, "%s" provided',
                    ProductInterface::class,
                    ClassUtils::getClass($product)
                )
            );
        }
    }

    // TODO: move all these private methods in another service and spec them!!
    private function doesProductNeedCompletenessCalculation(array $events, ?FamilyInterface $family): bool
    {
        foreach ($events as $event) {
            if ($event instanceof FamilyOfProductChanged || $event instanceof FamilyAddedToProduct) {
                return true;
            }
            if ($event instanceof ParentOfProductAdded || $event instanceof ParentOfProductChanged) {
                return true;
            }
            if ($event instanceof ValueAdded || $event instanceof ValueDeleted) {
                if (true === $this->isValueRequiredForCompleteness(
                        $event->attributeCode(),
                        $event->channelCode(),
                        $family
                    )) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isValueRequiredForCompleteness(
        string $attributeCode,
        ?string $channelCode,
        ?FamilyInterface $family
    ): bool {
        if (null === $family || !$family->hasAttributeCode($attributeCode)) {
            return false;
        }
        foreach ($family->getAttributeRequirements() as $requirement) {
            if ($requirement->isRequired() && $requirement->getAttributeCode() === $atributeCode &&
                (null === $channelCode || $channelCode === $requirement->getChannelCode())) {
                return true;
            }
        }

        return false;
    }

    private function doesProductNeedUniqueDataSynchro(array $events): bool
    {
        foreach ($events as $event) {
            if ($event instanceof ValueAdded || $event instanceof ValueDeleted || $event instanceof ValueEdited) {
                $attribute = $this->attributeRepository->findOneByIdentifier($event->attributeCode());

                return null !== $attribute && true === $attribute->isUnique();
            }
        }

        return false;
    }

    private function doesProductNeedReindexing(array $events): bool
    {
        return count($events) > 0;
    }
}
