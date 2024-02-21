<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MaskItemGeneratorForAttributeType
{
    /**
     * @param string $attributeCode
     * @param string $channelCode
     * @param string $localeCode
     * @param mixed  $value
     *
     * @return string[]
     */
    public function forRawValue(string $attributeCode, string $channelCode, string $localeCode, $value): array;

    /**
     * @return string[]
     */
    public function supportedAttributeTypes(): array;
}
