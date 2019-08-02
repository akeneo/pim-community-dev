<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

/**
 * This service removes all empty values from a rawValueCollections
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EmptyValuesCleaner
{
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
