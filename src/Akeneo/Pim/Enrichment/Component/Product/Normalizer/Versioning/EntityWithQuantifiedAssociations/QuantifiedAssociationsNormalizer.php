<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\EntityWithQuantifiedAssociations;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class QuantifiedAssociationsNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     *
     * @param EntityWithQuantifiedAssociationsInterface $entityWithQuantifiedAssociations
     */
    public function normalize($entityWithQuantifiedAssociations, $format = null, array $context = [])
    {
        $quantifiedAssociationsNormalized = $entityWithQuantifiedAssociations->normalizeQuantifiedAssociations();

        $results = [];
        foreach ($quantifiedAssociationsNormalized as $associationTypeCode => $linkTypes) {
            $results = array_merge(
                $results,
                $this->normalizeQuantifiedProductLinks($linkTypes['products'], (string) $associationTypeCode),
                $this->normalizeQuantifiedProductModelLinks($linkTypes['product_models'], (string) $associationTypeCode),
            );
        }

        return $results;
    }

    private function normalizeQuantifiedProductLinks(array $quantifiedProductLinks, string $associationTypeCode)
    {
        return [
            sprintf('%s-products', $associationTypeCode) => implode(',', $this->identifierProducts($quantifiedProductLinks)),
            sprintf('%s-products-quantity', $associationTypeCode) => implode('|', array_column($quantifiedProductLinks, 'quantity')),
        ];
    }

    private function normalizeQuantifiedProductModelLinks(array $quantifiedProductModelLinks, string $associationTypeCode)
    {
        return [
            sprintf('%s-product_models', $associationTypeCode) => implode(',', $this->identifierProducts($quantifiedProductModelLinks)),
            sprintf('%s-product_models-quantity', $associationTypeCode) => implode('|', array_column($quantifiedProductModelLinks, 'quantity')),
        ];
    }

    public function identifierProducts(array $quantifiedProductLinks): array
    {
        return array_map(function (array $quantifiedProduct) {
            $keyIdentifier = array_key_exists('uuid', $quantifiedProduct) ? 'uuid': 'identifier';
            return $quantifiedProduct[$keyIdentifier];
        }, $quantifiedProductLinks);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof EntityWithQuantifiedAssociationsInterface && $format === 'flat';
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
