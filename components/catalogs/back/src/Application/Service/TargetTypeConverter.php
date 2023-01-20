<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Service;

use Akeneo\Catalogs\Application\Exception\NoCompatibleAttributeTypeFoundException;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class TargetTypeConverter
{
    private const PIM_ATTRIBUTE_TYPES = [
        'string' => [
            'pim_catalog_text',
            'pim_catalog_textarea',
            'pim_catalog_simpleselect',
        ],
        'string+date-time' => [
            'pim_catalog_date',
        ],
    ];

    /**
     * @return string[]
     */
    public function toAttributeTypes(string $targetType, string $targetFormat = ''): array
    {
        $key = $targetType;

        if ('' !== $targetFormat) {
            $key = \sprintf('%s+%s', $targetType, $targetFormat);
        }

        if (!isset(self::PIM_ATTRIBUTE_TYPES[$key])) {
            throw new NoCompatibleAttributeTypeFoundException();
        }

        return self::PIM_ATTRIBUTE_TYPES[$key];
    }
}
