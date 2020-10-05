<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelCreated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductModelNotFoundException;
use Akeneo\Platform\Component\EventQueue\BusinessEventInterface;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCreatedAndUpdatedEventDataBuilder implements EventDataBuilderInterface
{
    private $productRepository;
    private $externalApiNormalizer;

    public function __construct(
        IdentifiableObjectRepositoryInterface $productRepository,
        NormalizerInterface $externalApiNormalizer
    ) {
        $this->productRepository = $productRepository;
        $this->externalApiNormalizer = $externalApiNormalizer;
    }

    public function supports(BusinessEventInterface $businessEvent): bool
    {
        return $businessEvent instanceof ProductModelUpdated || $businessEvent instanceof ProductModelCreated;
    }

    /**
     * @param ProductModelUpdated|ProductModelCreated $businessEvent
     */
    public function build(BusinessEventInterface $businessEvent): array
    {
        if (false === $this->supports($businessEvent)) {
            throw new \InvalidArgumentException();
        }

        $data = $businessEvent->data();

        $productModel = $this->productRepository->findOneByIdentifier($data['identifier']);
        if (null === $productModel) {
            throw new ProductModelNotFoundException($data['identifier']);
        }

        return [
            'resource' => $this->externalApiNormalizer->normalize($productModel, 'external_api'),
        ];
    }
}