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
                $this->normalizeQuantifiedProductLinks($linkTypes['products'], $associationTypeCode),
                $this->normalizeQuantifiedProductModelLinks($linkTypes['product_models'], $associationTypeCode),
            );
        }

        return $results;
    }

    private function normalizeQuantifiedProductLinks(array $quantifiedProductLinks, string $associationTypeCode)
    {
        return array_reduce(
            $quantifiedProductLinks,
            function (array $quantifiedLinksNormalized, array $quantifiedLink) use ($associationTypeCode) {
                $flatAssociationKey = sprintf('%s-products-%s', $associationTypeCode, $quantifiedLink['identifier']);
                $quantifiedLinksNormalized[$flatAssociationKey] = $quantifiedLink['quantity'];

                return $quantifiedLinksNormalized;
            },
            []
        );
    }

    private function normalizeQuantifiedProductModelLinks(array $quantifiedProductModelLinks, string $associationTypeCode)
    {
        return array_reduce(
            $quantifiedProductModelLinks,
            function (array $quantifiedLinksNormalized, array $quantifiedLink) use ($associationTypeCode) {
                $flatAssociationKey = sprintf('%s-product_models-%s', $associationTypeCode, $quantifiedLink['identifier']);
                $quantifiedLinksNormalized[$flatAssociationKey] = $quantifiedLink['quantity'];

                return $quantifiedLinksNormalized;
            },
            []
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EntityWithQuantifiedAssociationsInterface && $format === 'flat';
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
