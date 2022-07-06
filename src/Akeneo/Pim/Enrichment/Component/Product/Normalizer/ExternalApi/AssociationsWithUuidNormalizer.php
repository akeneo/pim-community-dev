<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AssociationsWithUuidNormalizer
{
    /**
     * This method keeps only the uuid value from the associated products.
     *
     * @param array $associations
     * Example
     * [
     *   'X_SELL' => [
     *     'products' => [['uuid' => '95341071-a0dd-47c6-81b1-315913952c43', 'identifier' => 'product_code_1']],
     *     'product_models' => [],
     *     'groups' => ['group_code_2']
     *   ],
     * ],
     *
     * @return array
     */
    public function normalize(array $associations): array
    {
        $result = [];
        foreach ($associations as $associationType => $associationsByType) {
            $result[$associationType] = [];
            foreach ($associationsByType as $entityType => $associationsByEntityType) {
                $result[$associationType][$entityType] = $entityType === 'products' ?
                    array_map(fn (array $associatedObject): ?string => $associatedObject['uuid'], $associationsByEntityType) :
                    $associationsByEntityType;
            }
        }

        return $result;
    }
}
