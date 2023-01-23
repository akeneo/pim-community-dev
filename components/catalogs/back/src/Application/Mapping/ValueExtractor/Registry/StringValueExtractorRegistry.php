<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Registry;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Exception\ValueExtractorNotFoundException;
use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StringValueExtractorRegistry implements ValueExtractorRegistryInterface
{
    /**
     * @param StringValueExtractorInterface[] $extractors
     */
    public function __construct(
        private readonly array $extractors,
    ) {
        foreach ($this->extractors as $extractor) {
            if (!$extractor instanceof StringValueExtractorInterface) {
                throw new \LogicException(
                    static::class . ' accepts only array of ' .
                    StringValueExtractorInterface::class . ' as argument.'
                );
            }
        }
    }

    public function extract(
        array $product,
        string $code,
        string $attributeType,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {
        foreach ($this->extractors as $extractor) {
            if ($extractor->supports($attributeType)) {
                return $extractor->extract(
                    $product,
                    $code,
                    $locale,
                    $scope,
                    $parameters,
                );
            }
        }

        throw new ValueExtractorNotFoundException();
    }
}
