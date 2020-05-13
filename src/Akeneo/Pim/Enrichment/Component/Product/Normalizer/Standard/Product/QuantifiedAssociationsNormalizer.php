<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;
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

        $ancestors = $this->getAncestors($entity);
        $associations = [];

        foreach ($ancestors as $ancestor) {
            if (!$ancestor instanceof EntityWithQuantifiedAssociationsInterface) {
                continue;
            }

            $associations = $this->mergeQuantifiedAssociations(
                $associations,
                $ancestor->normalizeQuantifiedAssociations()
            );
        }

        return $associations;
    }

    private function mergeQuantifiedAssociations(array $source, array $values): array
    {
        foreach ($values as $associationTypeCode => $association) {
            foreach ($association as $associationEntityType => $rows) {
                foreach ($rows as $row) {
                    $key = $this->searchKeyOfDuplicatedQuantifiedAssociation(
                        $source,
                        $associationTypeCode,
                        $associationEntityType,
                        $row
                    );

                    if (null !== $key) {
                        $source[$associationTypeCode][$associationEntityType][$key]['quantity'] = $row['quantity'];
                        continue;
                    }

                    $source[$associationTypeCode][$associationEntityType][] = $row;
                }
            }
        }

        return $source;
    }

    /**
     * Since we are using an unindexed array for the quantified associations,
     * we need to find if there is a row with the same identifier as the one we have and with its key,
     * we will be able to overwrite the quantity.
     *
     * For context, this is the structure:
     * [
     *      'PACK' => [
     *          'products' => [
     *              ['identifier' => 'foo', 'quantity' => 2],
     *              ['identifier' => 'bar', 'quantity' => 4],
     *          ]
     *      ]
     * ]
     *
     */
    private function searchKeyOfDuplicatedQuantifiedAssociation(
        array $source,
        string $associationTypeCode,
        string $associationEntityType,
        array $quantifiedAssociation
    ): ?int {
        $matchingSourceQuantifiedAssociations = array_filter(
            $source[$associationTypeCode][$associationEntityType] ?? [],
            function ($sourceQuantifiedAssociation) use ($quantifiedAssociation) {
                return $sourceQuantifiedAssociation['identifier'] === $quantifiedAssociation['identifier'];
            }
        );

        if (empty($matchingSourceQuantifiedAssociations)) {
            return null;
        }

        return array_keys($matchingSourceQuantifiedAssociations)[0];
    }

    /**
     * This function returns an array with the ancestors in the following order:
     * [product_model, product_variant_level_1, product_variant_level_2]
     * when given a product variant.
     * It will only look for parents of course, if an intermediate (or the product model) is given,
     * there will no children in the ancestors tree.
     *
     * @param EntityWithFamilyVariantInterface $entity
     * @return array
     */
    private function getAncestors(EntityWithFamilyVariantInterface $entity): array
    {
        $ancestors = [$entity];
        $current = $entity;

        while (null !== $parent = $current->getParent()) {
            $current = $parent;
            $ancestors[] = $current;
        }

        return array_reverse($ancestors);
    }
}
