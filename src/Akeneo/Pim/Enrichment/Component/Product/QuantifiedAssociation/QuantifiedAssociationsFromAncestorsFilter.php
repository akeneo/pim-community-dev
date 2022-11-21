<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * This class filter quantified associations that are existing in one of the parent of this entity.
 *
 * This is needed because we merge all the quantified associations from a product variant with
 * the quantified associations of its parents.
 * When this collection is then submitted to the product updater, it will contains both quantified associations from
 * the parents and from the current entity.
 * This filter make sure we are not persisting inherited quantified associations.
 */
class QuantifiedAssociationsFromAncestorsFilter
{
    private const PRODUCTS_QUANTIFIED_LINKS_KEY = 'products';
    private const PRODUCT_MODELS_QUANTIFIED_LINKS_KEY = 'product_models';

    /** @var QuantifiedAssociationsMerger */
    private $quantifiedAssociationsMerger;

    public function __construct(
        QuantifiedAssociationsMerger $quantifiedAssociationsMerger
    ) {
        $this->quantifiedAssociationsMerger = $quantifiedAssociationsMerger;
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

        $ancestorsQuantifiedAssociations = $this->quantifiedAssociationsMerger->normalizeAndMergeQuantifiedAssociationsFrom($ancestors);

        if (empty($ancestorsQuantifiedAssociations)) {
            return $data;
        }

        $filteredData = [];

        foreach ($data as $associationTypeCode => $associationTypeValues) {
            $filteredData[$associationTypeCode][self::PRODUCTS_QUANTIFIED_LINKS_KEY] = [];
            foreach ($associationTypeValues[self::PRODUCTS_QUANTIFIED_LINKS_KEY] ?? [] as $quantifiedLink) {
                $ancestorsQuantifiedLinks = $ancestorsQuantifiedAssociations[$associationTypeCode][self::PRODUCTS_QUANTIFIED_LINKS_KEY] ?? [];

                if (!in_array($quantifiedLink, $ancestorsQuantifiedLinks)) {
                    $filteredData[$associationTypeCode][self::PRODUCTS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink;
                }
            }

            $filteredData[$associationTypeCode][self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY] = [];
            foreach ($associationTypeValues[self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY] ?? [] as $quantifiedLink) {
                $ancestorsQuantifiedLinks = $ancestorsQuantifiedAssociations[$associationTypeCode][self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY] ?? [];

                if (!in_array($quantifiedLink, $ancestorsQuantifiedLinks)) {
                    $filteredData[$associationTypeCode][self::PRODUCT_MODELS_QUANTIFIED_LINKS_KEY][] = $quantifiedLink;
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
