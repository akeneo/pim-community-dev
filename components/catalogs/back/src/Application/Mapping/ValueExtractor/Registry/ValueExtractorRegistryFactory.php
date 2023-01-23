<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Registry;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Exception\ValueExtractorRegistryNotFoundException;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ValueExtractorRegistryFactory
{
    public function __construct(
        private readonly NumberValueExtractorRegistry $numberProductValueExtractorRegistry,
        private readonly StringValueExtractorRegistry $stringProductValueExtractorRegistry,
        private readonly StringDateTimeValueExtractorRegistry $stringDateTimeProductValueExtractorRegistry,
    ) {
    }

    public function build(string $targetType, ?string $targetFormat): ValueExtractorRegistryInterface
    {
        $registry = match($targetType) {
            'number' => $this->numberProductValueExtractorRegistry,
            'string' => match($targetFormat) {
                'date-time' => $this->stringDateTimeProductValueExtractorRegistry,
                default => $this->stringProductValueExtractorRegistry,
            },
            default => null,
        };

        if (null === $registry) {
            throw new ValueExtractorRegistryNotFoundException(\sprintf('No registry to extract value for target type "%s" found.', $targetType));
        }

        return $registry;
    }
}
