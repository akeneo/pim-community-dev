<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product\ProductValueNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ValuesNormalizer
{
    /** @var ProductValueNormalizer */
    private $valueNormalizer;

    /** @var RouterInterface */
    private $router;

    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(ProductValueNormalizer $valueNormalizer, RouterInterface $router, GetAttributes $getAttributes)
    {
        $this->valueNormalizer = $valueNormalizer;
        $this->router = $router;
        $this->getAttributes = $getAttributes;
    }

    public function normalize(ReadValueCollection $values): array
    {
        $attributeCodes = $values->getAttributeCodes();
        $attributes = $this->getAttributes->forCodes($attributeCodes);
        $attributesIndexedByCode = [];

        foreach ($attributes as $attribute) {
            $attributesIndexedByCode[$attribute->code()] = $attribute;
        }

        $normalizedValues = [];
        foreach ($values as $value) {
            $normalizedValue = $this->valueNormalizer->normalize($value, 'standard', ['attributes' => $attributesIndexedByCode]);
            if ($value instanceof MediaValue) {
                $normalizedValue = $this->addHalLink($value, $normalizedValue);
            }

            $normalizedValues[$value->getAttributeCode()][] = $normalizedValue;
        }

        return $normalizedValues;
    }

    private function addHalLink(MediaValue $value, array $normalizedValue): array
    {
        $route = $this->router->generate(
            'pim_api_media_file_download',
            ['code' => $value->getData()->getKey()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $download = new Link('download', $route);
        $normalizedValue['_links'] = $download->toArray();

        return $normalizedValue;
    }
}
