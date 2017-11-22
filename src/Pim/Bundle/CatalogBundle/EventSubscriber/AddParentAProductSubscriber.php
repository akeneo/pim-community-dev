<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;
use Pim\Component\Catalog\EntityWithFamily\Event\ParentWasAddedToProduct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * When a product is turn into a variant product we need to update the database and change the object in the
 * doctrine unit of work.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddParentAProductSubscriber implements EventSubscriberInterface
{
    /** @var Query\TurnProductIntoVariantProduct */
    private $turnProductIntoVariantProduct;

    /**
     * AddParentAProductSubscriber constructor.
     *
     * @param Query\TurnProductIntoVariantProduct $turnProductIntoVariantProduct
     */
    public function __construct(Query\TurnProductIntoVariantProduct $turnProductIntoVariantProduct)
    {
        $this->turnProductIntoVariantProduct = $turnProductIntoVariantProduct;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ParentWasAddedToProduct::EVENT_NAME => 'turnProductIntoVariantProduct'
        ];
    }

    /**
     * @param ParentWasAddedToProduct $event
     */
    public function turnProductIntoVariantProduct(ParentWasAddedToProduct $event): void
    {
        ($this->turnProductIntoVariantProduct)($event->convertedProduct());
    }
}
