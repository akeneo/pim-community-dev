<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\NotGrantedCategoryException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductNotFoundException;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCreatedAndUpdatedEventDataBuilder implements EventDataBuilderInterface
{
    private GetConnectorProducts $getConnectorProductsQuery;
    private NormalizerInterface$externalApiNormalizer;

    public function __construct(
        GetConnectorProducts $getConnectorProductsQuery,
        NormalizerInterface $externalApiNormalizer
    ) {
        $this->getConnectorProductsQuery = $getConnectorProductsQuery;
        $this->externalApiNormalizer = $externalApiNormalizer;
    }

    public function supports(BusinessEventInterface $businessEvent): bool
    {
        return $businessEvent instanceof ProductUpdated || $businessEvent instanceof ProductCreated;
    }

    /**
     * @param ProductCreated|ProductUpdated $businessEvent
     * @throws NotGrantedCategoryException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function build(BusinessEventInterface $businessEvent, int $userId): array
    {
        if (false === $this->supports($businessEvent)) {
            throw new \InvalidArgumentException();
        }

        $data = $businessEvent->data();

        try {
            $product = $this->getConnectorProductsQuery->fromProductIdentifier($data['identifier'], $userId);
        } catch (ObjectNotFoundException $e) {
            throw new ProductNotFoundException($data['identifier']);
        }

        return [
            'resource' => $this->externalApiNormalizer->normalize($product, 'external_api'),
        ];
    }
}
