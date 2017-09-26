<?php

namespace Pim\Component\Api\Normalizer;

use Pim\Component\Api\Hal\Link;
use Pim\Component\Api\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Normalizer\Standard\Product\PropertiesNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $productNormalizer;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var RouterInterface */
    protected $router;

    /**
     * @param NormalizerInterface          $productNormalizer
     * @param AttributeRepositoryInterface $attributeRepository
     * @param RouterInterface              $router
     */
    public function __construct(
        NormalizerInterface $productNormalizer,
        AttributeRepositoryInterface $attributeRepository,
        RouterInterface $router
    ) {
        $this->productNormalizer = $productNormalizer;
        $this->attributeRepository = $attributeRepository;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $productStandard = $this->productNormalizer->normalize($product, 'standard', $context);

        // TODO: for now, the web api format remains the same, cf API-385
        $productStandardWithDeprecatedVariantGroup = [];
        foreach ($productStandard as $field => $data) {
            $productStandardWithDeprecatedVariantGroup[$field] = $data;
            if ($field === PropertiesNormalizer::FIELD_GROUPS) {
                $productStandardWithDeprecatedVariantGroup['variant_group'] = null;
            }
        }
        $productStandard = $productStandardWithDeprecatedVariantGroup;

        $identifier = $this->attributeRepository->getIdentifierCode();

        if (isset($productStandard['values'][$identifier])) {
            unset($productStandard['values'][$identifier]);
        }

        $mediaAttributeCodes = $this->attributeRepository->getMediaAttributeCodes();
        foreach ($productStandard['values'] as $attributeCode => $values) {
            // if $context['attributes'] is defined, returns only these attributes
            if (isset($context['attributes']) && !in_array($attributeCode, $context['attributes'])) {
                unset($productStandard['values'][$attributeCode]);
            } elseif (in_array($attributeCode, $mediaAttributeCodes)) {
                $productStandard['values'][$attributeCode] = $this->addDownloadLink($values);
            }
        }

        if (empty($productStandard['values'])) {
            $productStandard['values'] = (object) $productStandard['values'];
        }

        if (empty($productStandard['associations'])) {
            $productStandard['associations'] = (object) $productStandard['associations'];
        }

        return $productStandard;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && 'external_api' === $format;
    }

    /**
     * @param array  $values
     *
     * @return array
     */
    protected function addDownloadLink(array $values)
    {
        foreach ($values as $index => $value) {
            if (null !== $value['data']) {
                $route = $this->router->generate(
                    'pim_api_media_file_download',
                    ['code' => $value['data']],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                $download = new Link('download', $route);
                $values[$index]['_links'] = $download->toArray();
            }
        }

        return $values;
    }
}
