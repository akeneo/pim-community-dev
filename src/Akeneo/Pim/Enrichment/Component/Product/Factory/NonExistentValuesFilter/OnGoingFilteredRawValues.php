<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

/**
 * Here is the format we expect to work on.
 *
 * [
 *     'TEXT' => [
 *         'description' => [
 *             [
 *                 'identifier' => 'productA',
 *                 'values' => [
 *                     'ecommerce' => [
 *                         'en_US' => 'a_description',
 *                         'fr_FR' => 'une description'
 *                     ]
 *                 ]
 *             ],
 *             [
 *                 'identifier' => 'productB',
 *                 'values' => [
 *                     '<all_channels>' => [
 *                         '<all_locales>' => 'totototototo',
 *                     ]
 *                 ]
 *             ]
 *         ]
 *     ]
 * ];
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class OnGoingFilteredRawValues
{
    /** @var array */
    private $filteredRawValuesCollectionIndexedByType;

    /** @var array */
    private $nonFilteredRawValuesCollectionIndexedByType;

    public function __construct(array $filteredRawValuesCollectionIndexedByType, array $nonFilteredRawValuesCollectionByType)
    {
        $this->filteredRawValuesCollectionIndexedByType = $filteredRawValuesCollectionIndexedByType;
        $this->nonFilteredRawValuesCollectionIndexedByType = $nonFilteredRawValuesCollectionByType;
    }

    public static function fromNonFilteredValuesCollectionIndexedByType(array $nonFilteredValuesCollectionIndexedByType)
    {
        return new self([], $nonFilteredValuesCollectionIndexedByType);
    }

    public function notFilteredValuesOfTypes(string ...$attributeTypes): array
    {
        $result = [];

        foreach ($attributeTypes as $attributeType) {
            $result = $result + ($this->nonFilteredRawValuesCollectionIndexedByType[$attributeType] ?? []);
        }

        return $result;
    }

    public function addFilteredValuesIndexedByType(array $filteredValueCollectionsIndexedByType): OnGoingFilteredRawValues
    {
        $attributeTypesHandled = array_keys($filteredValueCollectionsIndexedByType);

        $nonFilteredRawValues = $this->nonFilteredRawValuesCollectionIndexedByType;

        foreach ($attributeTypesHandled as $attributeTypeHandled) {
            unset($nonFilteredRawValues[$attributeTypeHandled]);
        }

        $newFilteredRawValues = $this->filteredRawValuesCollectionIndexedByType + $filteredValueCollectionsIndexedByType;

        return new self($newFilteredRawValues, $nonFilteredRawValues);
    }

    public function toRawValueCollection(): array
    {
        $products = [];

        foreach ($this->filteredRawValuesCollectionIndexedByType as $type => $attributeCodes) {
            foreach ($attributeCodes as $attributeCode => $values) {
                foreach ($values as $value) {
                    $products[$value['identifier']][$attributeCode] = $value['values'];
                }
            }
        }

        return $products;
    }

    public function filteredRawValuesCollectionIndexedByType(): array
    {
        return $this->filteredRawValuesCollectionIndexedByType;
    }

    public function nonFilteredRawValuesCollectionIndexedByType(): array
    {
        return $this->nonFilteredRawValuesCollectionIndexedByType;
    }
}
