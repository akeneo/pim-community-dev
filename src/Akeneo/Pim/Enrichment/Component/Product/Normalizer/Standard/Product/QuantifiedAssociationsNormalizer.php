<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation\QuantifiedAssociationsMerger;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize quantified associations into an array
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationsNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var QuantifiedAssociationsMerger */
    private $quantifiedAssociationsMerger;

    public function __construct(
        QuantifiedAssociationsMerger $quantifiedAssociationMerger
    ) {
        $this->quantifiedAssociationsMerger = $quantifiedAssociationMerger;
    }

    /**
     * {@inheritdoc}
     *
     * @param EntityWithQuantifiedAssociationsInterface $entity
     */
    public function normalize($entity, $format = null, array $context = [])
    {
        return $this->normalizeWithParentsAssociations($entity, $format, $context);
    }

    public function normalizeWithParentsAssociations($entity, $format = null, array $context = [])
    {
        if (!$entity instanceof EntityWithFamilyVariantInterface) {
            return $this->normalizeWithoutParentsAssociations($entity, $format, $context);
        }

        $entities = $this->getAncestors($entity);
        $entities[] = $entity;

        return $this->quantifiedAssociationsMerger->normalizeAndMergeQuantifiedAssociationsFrom($entities);
    }

    public function normalizeWithoutParentsAssociations($entity, $format = null, array $context = [])
    {
        return $entity->normalizeQuantifiedAssociations();
    }

    public function normalizeOnlyParentsAssociations($entity, $format = null, array $context = [])
    {
        if (!$entity instanceof EntityWithFamilyVariantInterface) {
            return [];
        }

        $entities = $this->getAncestors($entity);

        return $this->quantifiedAssociationsMerger->normalizeAndMergeQuantifiedAssociationsFrom($entities);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof EntityWithQuantifiedAssociationsInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * This function returns an array with the ancestors in the following order:
     * [product_model, product_variant_level_1, product_variant_level_2]
     * It will only returns the parents, not the current entity.
     *
     * @param EntityWithFamilyVariantInterface $entity
     * @return array
     */
    private function getAncestors(EntityWithFamilyVariantInterface $entity): array
    {
        $ancestors = [];
        $current = $entity;

        while (null !== $parent = $current->getParent()) {
            $current = $parent;
            $ancestors[] = $current;
        }

        return array_reverse($ancestors);
    }
}
