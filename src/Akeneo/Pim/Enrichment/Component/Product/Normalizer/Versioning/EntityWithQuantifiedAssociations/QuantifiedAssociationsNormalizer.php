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
            foreach ($linkTypes['products'] as $quantifiedLink) {
                $flatAssociationKey = $associationTypeCode . '-products-' . $quantifiedLink['identifier'];
                $results[$flatAssociationKey] = $quantifiedLink['quantity'];
            }

            foreach ($linkTypes['product_models'] as $quantifiedLink) {
                $flatAssociationKey = $associationTypeCode . '-product_models-' . $quantifiedLink['identifier'];
                $results[$flatAssociationKey] = $quantifiedLink['quantity'];
            }
        }

        return $results;
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
