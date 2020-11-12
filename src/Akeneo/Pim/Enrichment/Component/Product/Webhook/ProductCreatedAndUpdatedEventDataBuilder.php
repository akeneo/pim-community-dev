<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\NotGrantedCategoryException;
use Akeneo\Platform\Component\EventQueue\BulkEventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCreatedAndUpdatedEventDataBuilder implements EventDataBuilderInterface
{
    private ProductQueryBuilderFactoryInterface $pqbFactory;
    private GetConnectorProducts $getConnectorProductsQuery;
    private ConnectorProductNormalizer $connectorProductNormalizer;

    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        GetConnectorProducts $getConnectorProductsQuery,
        ConnectorProductNormalizer $connectorProductNormalizer
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->getConnectorProductsQuery = $getConnectorProductsQuery;
        $this->connectorProductNormalizer = $connectorProductNormalizer;
    }

    /**
     * @param BulkEventInterface $event
     */
    public function supports(object $event): bool
    {
        if (false === $event instanceof BulkEventInterface) {
            return false;
        }

        foreach ($event->getEvents() as $event) {
            if (false === $event instanceof ProductCreated && false === $event instanceof ProductUpdated) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param BulkEventInterface $bulkEvent
     *
     * @throws NotGrantedCategoryException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function build(object $bulkEvent, int $userId): array
    {
        if (false === $this->supports($bulkEvent)) {
            throw new \InvalidArgumentException();
        }

        $identifiers = [];

        /** @var ProductCreated|ProductUpdated $event */
        foreach ($bulkEvent->getEvents() as $event) {
            $identifiers[] = $event->getIdentifier();
        }

        $pqb = $this->pqbFactory->create(['limit' => count($identifiers)]);
        $pqb->addFilter('identifier', Operators::IN_LIST, $identifiers);

        $productList = $this->getConnectorProductsQuery->fromProductQueryBuilder(
            $pqb,
            $userId,
            null,
            null,
            null
        );

        // TODO : log products not found

        return \array_map(function ($identifier) use ($productList) {
            foreach ($productList->connectorProducts() as $connectorProduct) {
                /** @var ConnectorProduct $connectorProduct */
                if ($identifier === $connectorProduct->identifier()) {
                    return [
                        'resource' => $this->connectorProductNormalizer->normalizeConnectorProduct($connectorProduct)
                    ];
                }
            }

            return null;
        }, $identifiers);
    }
}
