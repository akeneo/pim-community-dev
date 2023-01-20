<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Registry;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Exception\ValueExtractorNotFoundException;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ValueExtractorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ValueExtractorRegistry
{
    /**
     * @param array<ValueExtractorInterface> $extractors
     */
    public function __construct(
        private readonly array $extractors,
    ) {
        foreach ($this->extractors as $extractor) {
            if (!$extractor instanceof ValueExtractorInterface) {
                throw new \LogicException(
                    static::class . ' accepts only array of ' .
                    ValueExtractorInterface::class . ' as argument.'
                );
            }
        }
    }

    public function find(string $sourceType, string $targetType, ?string $targetFormat): ValueExtractorInterface
    {
        foreach ($this->extractors as $extractor) {
            if ($sourceType === $extractor->getSupportedSourceType()
                && $targetType === $extractor->getSupportedTargetType()
                && $targetFormat === $extractor->getSupportedTargetFormat()
            ) {
                return $extractor;
            }
        }

        throw new ValueExtractorNotFoundException();
    }
}
