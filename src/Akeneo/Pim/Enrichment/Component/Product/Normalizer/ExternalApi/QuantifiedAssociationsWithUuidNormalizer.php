<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QuantifiedAssociationsWithUuidNormalizer
{
    /**
     * This method keeps only uuid identifier and quantity values from the associated products.
     *
     * @param array $quantifiedAssociations
     * Example
     * [
     *   'X_SELL' => [
     *     'products' => [
     *       ['uuid' => '95341071-a0dd-47c6-81b1-315913952c43', 'identifier' => 'product_code_1', 'quantity' => 8]
     *     ],
     *     'product_models' => [],
     *     'groups' => ['group_code_2']
     *   ],
     * ],
     *
     * @return array
     */
    public function normalize(array $quantifiedAssociations): array
    {
        $result = [];
        foreach ($quantifiedAssociations as $associationType => $associationsByType) {
            foreach ($associationsByType as $entityType => $associationsByEntityType) {
                $result[$associationType][$entityType] = $entityType === 'products' ?
                    array_map(
                        fn (array $associatedObject): array => array_filter(
                            $associatedObject,
                            fn (string $key): bool => in_array($key, ['uuid', 'quantity']),
                            ARRAY_FILTER_USE_KEY
                        ),
                        $associationsByEntityType
                    ) :
                    $result[$associationType][$entityType] = $associationsByEntityType;
            }
        }

        return $result;
    }
}
