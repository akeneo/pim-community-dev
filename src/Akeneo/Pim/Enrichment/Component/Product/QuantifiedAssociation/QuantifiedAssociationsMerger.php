<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * This class merge the quantified associations of a list of entities, this is used for inherited associations
 * between product models and product variants.
 *
 * Given an array of entities, eg [product_model_1, product_variant_1, product_variant_2],
 * the returned values will be an array of normalized quantified associations from all this entities.
 * When the same association is defined at different levels, the quantity in the child will override
 * the one of the parent.
 */
class QuantifiedAssociationsMerger
{
    public function normalizeAndMergeQuantifiedAssociationsFrom(array $entitiesWithQuantifiedAssociations): array
    {
        $associations = [];

        foreach ($entitiesWithQuantifiedAssociations as $entity) {
            if (!$entity instanceof EntityWithQuantifiedAssociationsInterface) {
                continue;
            }

            $associations = $this->mergeQuantifiedAssociations(
                $associations,
                $entity->normalizeQuantifiedAssociations()
            );
        }

        return $associations;
    }

    /**
     * This method merge into $array_1 the associations from $array_2.
     * If an identifier is found in both, the quantity in $array_2 will overwrite the existing one in $array_1.
     */
    private function mergeQuantifiedAssociations(array $quantifiedAssociations1, array $quantifiedAssociations2): array
    {
        foreach ($quantifiedAssociations2 as $associationTypeCode => $association) {
            foreach ($association as $associationEntityType => $quantifiedLinks) {
                if (!isset($quantifiedAssociations1[$associationTypeCode][$associationEntityType])) {
                    $quantifiedAssociations1[$associationTypeCode][$associationEntityType] = [];
                }

                foreach ($quantifiedLinks as $quantifiedLink) {
                    $key = $this->searchKeyOfDuplicatedQuantifiedAssociation(
                        $quantifiedAssociations1,
                        $associationTypeCode,
                        $associationEntityType,
                        $quantifiedLink
                    );

                    if (null !== $key) {
                        $quantifiedAssociations1[$associationTypeCode][$associationEntityType][$key]['quantity'] = $quantifiedLink['quantity'];
                        continue;
                    }

                    $quantifiedAssociations1[$associationTypeCode][$associationEntityType][] = $quantifiedLink;
                }
            }
        }

        return $quantifiedAssociations1;
    }

    /**
     * Since we are using an unindexed array for the quantified associations,
     * we need to find if there is a row with the same identifier as the one we have.
     * With its key, we will be able to overwrite the quantity.
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
        array $quantifiedLink
    ): ?int {
        $matchingSourceQuantifiedAssociations = array_filter(
            $source[$associationTypeCode][$associationEntityType] ?? [],
            function ($sourceQuantifiedAssociation) use ($quantifiedLink) {
                return $sourceQuantifiedAssociation['identifier'] === $quantifiedLink['identifier'];
            }
        );

        if (empty($matchingSourceQuantifiedAssociations)) {
            return null;
        }

        return array_keys($matchingSourceQuantifiedAssociations)[0];
    }
}
