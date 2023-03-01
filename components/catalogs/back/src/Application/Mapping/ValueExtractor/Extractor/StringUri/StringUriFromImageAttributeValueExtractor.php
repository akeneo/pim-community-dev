<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringUri;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\StringValueExtractorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class StringUriFromImageAttributeValueExtractor implements StringValueExtractorInterface
{
    public function __construct(private RouterInterface $router)
    {
    }

    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | string {
        /** @var mixed $value */
        $value = $product['raw_values'][$code][$scope][$locale] ?? null;

        return \is_string($value) ?
            $this->router->generate('pim_api_media_file_download', [
                'code' => $value,
            ], RouterInterface::ABSOLUTE_URL) : null;
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_ATTRIBUTE_IMAGE;
    }

    public function getSupportedTargetType(): string
    {
        return self::TARGET_TYPE_STRING;
    }

    public function getSupportedTargetFormat(): ?string
    {
        return self::TARGET_FORMAT_URI;
    }
}
