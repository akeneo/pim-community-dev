<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation\QuantifiedAssociationsMerger;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize associations into an array
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
        $mergeAncestors = (bool)($context['merge_ancestors'] ?? true);

        if ($mergeAncestors && $entity instanceof EntityWithFamilyVariantInterface) {
            return $this->normalizeAndMergeAncestorsAssociations($entity);
        }

        return $entity->normalizeQuantifiedAssociations();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof EntityWithQuantifiedAssociationsInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    private function normalizeAndMergeAncestorsAssociations($entity): array
    {
        if (!$entity instanceof EntityWithQuantifiedAssociationsInterface &&
            !$entity instanceof EntityWithFamilyVariantInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The given object must implement %s and %s',
                    EntityWithQuantifiedAssociationsInterface::class,
                    EntityWithFamilyVariantInterface::class
                )
            );
        }

        $entities = $this->getAncestors($entity);
        $entities[] = $entity;

        return $this->quantifiedAssociationsMerger->normalizeAndMergeQuantifiedAssociationsFrom($entities);
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
