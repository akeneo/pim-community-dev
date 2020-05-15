<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithQuantifiedAssociationsInterface;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            foreach ($association as $associationEntityType => $rows) {
                if (!isset($quantifiedAssociations1[$associationTypeCode][$associationEntityType])) {
                    $quantifiedAssociations1[$associationTypeCode][$associationEntityType] = [];
                }

                foreach ($rows as $row) {
                    $key = $this->searchKeyOfDuplicatedQuantifiedAssociation(
                        $quantifiedAssociations1,
                        $associationTypeCode,
                        $associationEntityType,
                        $row
                    );

                    if (null !== $key) {
                        $quantifiedAssociations1[$associationTypeCode][$associationEntityType][$key]['quantity'] = $row['quantity'];
                        continue;
                    }

                    $quantifiedAssociations1[$associationTypeCode][$associationEntityType][] = $row;
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
}
