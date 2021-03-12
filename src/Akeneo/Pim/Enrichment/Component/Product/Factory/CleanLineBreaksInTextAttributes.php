<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Structure\Component\AttributeTypes;

class CleanLineBreaksInTextAttributes
{
    public static function cleanFromRawValuesFormat(array $rawValueCollections, array $attributes): array
    {
        foreach ($rawValueCollections as $productIdentifier => $valueCollection) {
            foreach ($valueCollection as $attributeCode => $channelRawValue) {
                $attribute = $attributes[$attributeCode];
                if ($attribute->type() !== AttributeTypes::TEXT) {
                    continue;
                }
                foreach ($channelRawValue as $channelCode => $localeRawValue) {
                    foreach ($localeRawValue as $localeCode => $data) {
                        if (!is_string($data)) {
                            continue;
                        }
                        $cleanedData = preg_replace(
                            '/[\r\n|\r|\n]+/',
                            ' ',
                            $data);
                        $rawValueCollections[$productIdentifier][$attributeCode][$channelCode][$localeCode] = $cleanedData;
                    }
                }
            }
        }

        return $rawValueCollections;
    }
}
