<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

/**
 * This service removes all empty values from raw values stored in the database.
 * Do note that we don't store null values or empty arrays in the database. However, the consistency of the data in not guaranteed
 * in the JSON of the raw values.
 *
 * Therefore, when filtering on non existing data, we can have values with empty arrays or empty data.
 * That's why we must filter it after doing it.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EmptyValuesCleaner
{
    /**
     * @param array $rawValueCollections array of raw value collections
     * It allows to filter several raw values at once from different product or product models.
     * [
     *    'product_identifier_1' => ['attribute_code' => [...], 'attribute_code_2' => [...]],
     *    'product_identifier_2' => ['attribute_code' => [...]]
     * ]
     * @return array same format as passed in the parameter, but values are filtered
     */
    public function cleanAllValues(array $rawValueCollections): array
    {
        $results = [];

        foreach ($rawValueCollections as $identifier => $rawValues) {
            foreach ($rawValues as $attributeCode => $channelValues) {
                foreach ($channelValues as $channel => $localeValues) {
                    foreach ($localeValues as $locale => $data) {
                        if ($this->isFilled($data)) {
                            $results[$identifier][$attributeCode][$channel][$locale] = $data;
                        }
                    }
                }
            }
        }

        return $results;
    }

    private function isFilled($data): bool
    {
        if (null === $data) {
            return false;
        }

        if ('' === $data) {
            return false;
        }

        if (is_array($data)) {
            foreach ($data as $subValue) {
                if ($this->isFilled($subValue)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }
}
