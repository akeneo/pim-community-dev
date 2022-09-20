<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ConnectorProductWithUuidNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi\ValuesNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

final class ProductWithUuidNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(
        private GetConnectorProducts $getConnectorProducts,
        private ConnectorProductWithUuidNormalizer $connectorProductNormalizer,
        private ValuesNormalizer $valuesNormalizer,
    ) {
    }

    public function normalize($product, string $format = null, array $context = []): array
    {
        Assert::isInstanceOf($product, ProductInterface::class);
        Assert::integer($context['userId'] ?? null);

        $normalizedValues = $this->valuesNormalizer->normalize(new ReadValueCollection($product->getValues()->toArray()));

        return \array_replace(
            $this->connectorProductNormalizer->normalizeConnectorProduct(
                $this->getConnectorProducts->fromProductUuid($product->getUuid(), $context['userId'])
            ),
            ['values' => [] === $normalizedValues ? (object) [] : $normalizedValues]
        );
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof ProductInterface && 'external_api' === $format;
    }
}
