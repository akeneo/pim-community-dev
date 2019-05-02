<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\EmptyValuesCleaner;

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
final class OnGoingCleanedRawValues
{
    /** @var array */
    private $cleanedRawValuesCollectionIndexedByType;

    /** @var array */
    private $nonCleanedRawValuesCollectionIndexedByType;

    public function __construct(array $cleanedRawValuesCollectionIndexedByType, array $nonCleanedRawValuesCollectionByType)
    {
        $this->cleanedRawValuesCollectionIndexedByType = $cleanedRawValuesCollectionIndexedByType;
        $this->nonCleanedRawValuesCollectionIndexedByType = $nonCleanedRawValuesCollectionByType;
    }

    public static function fromNonCleanedValuesCollectionIndexedByType(array $nonCleanedValuesCollectionIndexedByType)
    {
        return new self([], $nonCleanedValuesCollectionIndexedByType);
    }

    public function nonCleanedValuesOfTypes(string ...$attributeTypes): array
    {
        $result = [];

        foreach ($attributeTypes as $attributeType) {
            $result = $result + ($this->nonCleanedRawValuesCollectionIndexedByType[$attributeType] ?? []);
        }

        return $result;
    }

    public function addCleanedValuesIndexedByType(array $cleanedValuesIndexedByType): OnGoingCleanedRawValues
    {
        $attributeTypesHandled = array_keys($cleanedValuesIndexedByType);

        $nonCleanedRawValueCollection = $this->nonCleanedRawValuesCollectionIndexedByType;

        foreach ($attributeTypesHandled as $attributeTypeHandled) {
            unset($nonCleanedRawValueCollection[$attributeTypeHandled]);
            if ($cleanedValuesIndexedByType[$attributeTypeHandled] === []) {
                unset($cleanedValuesIndexedByType[$attributeTypeHandled]);
            }
        }

        $newCleanedValues = $this->cleanedRawValuesCollectionIndexedByType + $cleanedValuesIndexedByType;

        return new self($newCleanedValues, $nonCleanedRawValueCollection);
    }

    public function toRawValueCollection(): array
    {
        $products = [];

        foreach ($this->cleanedRawValuesCollectionIndexedByType as $type => $attributeCodes) {
            foreach ($attributeCodes as $attributeCode => $values) {
                foreach ($values as $value) {
                    $products[$value['identifier']][$attributeCode] = $value['values'];
                }
            }
        }

        return $products;
    }

    public function cleanedRawValuesCollectionIndexedByType(): array
    {
        return $this->cleanedRawValuesCollectionIndexedByType;
    }

    public function nonCleanedRawValuesCollectionIndexedByType(): array
    {
        return $this->nonCleanedRawValuesCollectionIndexedByType;
    }
}
