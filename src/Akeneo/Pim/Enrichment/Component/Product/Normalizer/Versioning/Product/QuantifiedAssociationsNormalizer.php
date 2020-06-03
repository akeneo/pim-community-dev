<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class QuantifiedAssociationsNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    protected $supportedFormats = ['flat'];

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
            foreach ($linkTypes['products'] as $productLink) {
                $flatAssociationKey = $associationTypeCode . '-products-' . $productLink['identifier'];
                $results[$flatAssociationKey] = $productLink['quantity'];
            }

            foreach ($linkTypes['product_models'] as $productModelLink) {
                $flatAssociationKey = $associationTypeCode . '-product_models-' . $productModelLink['identifier'];
                $results[$flatAssociationKey] = $productModelLink['quantity'];
            }
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EntityWithQuantifiedAssociationsInterface && in_array($format, $this->supportedFormats);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
