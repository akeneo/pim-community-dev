<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Before saving a product, we merge not granted data (categories, associated products and values) in product entity.
 *
 * If user is not the owner of the product, an exception is thrown if he tries to update it.
 * If product is new, there is no check on the own, the product will be created.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MergeNotGrantedProductDataSubscriber implements EventSubscriberInterface
{
    /** @var NotGrantedDataMergerInterface */
    private $categoryMerger;

    /** @var NotGrantedDataMergerInterface */
    private $associationMerger;

    /**
     * @param NotGrantedDataMergerInterface $categoryMerger
     * @param NotGrantedDataMergerInterface $associationMerger
     */
    public function __construct(
        NotGrantedDataMergerInterface $categoryMerger,
        NotGrantedDataMergerInterface $associationMerger
    ){
        $this->categoryMerger = $categoryMerger;
        $this->associationMerger = $associationMerger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [StorageEvents::PRE_SAVE => ['mergeNotGrantedData', 200]];
    }

    /**
     * @param GenericEvent $event
     */
    public function mergeNotGrantedData(GenericEvent $event): void
    {
        $product = $event->getSubject();

        if (!$product instanceof ProductInterface) {
            return;
        }

        $this->categoryMerger->merge($product);
        $this->associationMerger->merge($product);
    }
}
