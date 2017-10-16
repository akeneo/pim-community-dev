<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber\Scalability;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\Exception\TooManyEntitiesException;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Thrown a TooManyEntitiesException when the user raised the product scalability threshold
 * The thresholds are defined for each entity to guarantee an optimal use of Akeneo PIM on a standard infrastructure
 * Each threshold can be increased depending on the project custom code, infrastructure and configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCreationThresholdSubscriber implements EventSubscriberInterface
{
    /** @var ProductRepositoryInterface */
    private $repository;
    /** @var int */
    private $threshold;
    /** @var bool */
    private $enabled;

    /**
     * @param ProductRepositoryInterface $repository
     * @param int                        $threshold
     * @param bool                       $enabled
     */
    public function __construct(ProductRepositoryInterface $repository, int $threshold, bool $enabled)
    {
        $this->repository = $repository;
        $this->threshold = $threshold;
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() : array
    {
        return [
            StorageEvents::PRE_SAVE => ['checkEntityMaximumThreshold'],
        ];
    }

    /**
     * Check if the threshold is reached
     *
     * @param GenericEvent $event
     */
    public function checkEntityMaximumThreshold(GenericEvent $event) : void
    {
        if (!$this->enabled) {
            return;
        }

        $product = $event->getSubject();
        if ($product instanceof ProductInterface && null === $product->getId()) {
            $total = $this->repository->countAll();
            if ($total >= $this->threshold) {
                throw new TooManyEntitiesException(
                    sprintf(
                        '%d %s have already been created, to create more %s you have to increase the '.
                        '"%s" parameter (current value %d)',
                        $total,
                        'products',
                        'products',
                        'scalability_thresholds_catalog_product',
                        $this->threshold
                    )
                );
            }
        }
    }
}
