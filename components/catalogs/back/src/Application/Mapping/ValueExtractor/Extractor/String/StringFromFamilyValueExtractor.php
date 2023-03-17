<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\String;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;
use Akeneo\Catalogs\Application\Persistence\Family\GetFamilyLabelByCodeAndLocaleQueryInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StringFromFamilyValueExtractor implements StringValueExtractorInterface
{
    public function __construct(
        readonly private GetFamilyLabelByCodeAndLocaleQueryInterface $getFamilyLabelByCodeAndLocaleQuery,
    ) {
    }

    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {
        return $this->getFamilyLabelByCodeAndLocaleQuery->execute(
            $product['family_code'],
            $parameters['label_locale'],
        );
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_FAMILY;
    }

    public function getSupportedTargetType(): string
    {
        return self::TARGET_TYPE_STRING;
    }

    public function getSupportedTargetFormat(): ?string
    {
        return null;
    }
}
