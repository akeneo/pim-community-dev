<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QuantifiedAssociationsFromAncestorsFilter
{
    /** @var QuantifiedAssociationsMerger */
    private $quantifiedAssociationMerger;

    public function __construct(
        QuantifiedAssociationsMerger $quantifiedAssociationMerger
    ) {
        $this->quantifiedAssociationMerger = $quantifiedAssociationMerger;
    }

    public function filter(array $data, EntityWithQuantifiedAssociationsInterface $entity)
    {
        if (!$entity instanceof EntityWithFamilyVariantInterface) {
            return $data;
        }

        $ancestors = $this->getAncestors($entity);

        if (empty($ancestors)) {
            return $data;
        }

        $ancestorsQuantifiedAssociations = $this->quantifiedAssociationMerger->normalizeAndMergeQuantifiedAssociationsFrom($ancestors);

        if (empty($ancestorsQuantifiedAssociations)) {
            return $data;
        }

        $filteredData = [];

        foreach ($data as $associationTypeCode => $associationTypeValues) {
            foreach ($associationTypeValues['products'] as $quantifiedLink) {
                $ancestorsQuantifiedLinks = $ancestorsQuantifiedAssociations[$associationTypeCode]['products'] ?? [];

                if (!in_array($quantifiedLink, $ancestorsQuantifiedLinks)) {
                    $filteredData[$associationTypeCode]['products'][] = $quantifiedLink;
                }
            }

            foreach ($associationTypeValues['product_models'] as $quantifiedLink) {
                $ancestorsQuantifiedLinks = $ancestorsQuantifiedAssociations[$associationTypeCode]['product_models'] ?? [];

                if (!in_array($quantifiedLink, $ancestorsQuantifiedLinks)) {
                    $filteredData[$associationTypeCode]['product_models'][] = $quantifiedLink;
                }
            }
        }

        return $filteredData;
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
