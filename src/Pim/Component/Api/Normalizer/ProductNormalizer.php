<?php

namespace Pim\Component\Api\Normalizer;

use Pim\Component\Api\Hal\Link;
use Pim\Component\Api\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;
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
        $identifier = $this->attributeRepository->getIdentifierCode();

        if (isset($productStandard['values'][$identifier])) {
            unset($productStandard['values'][$identifier]);
        }

        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($productStandard['values']));
        foreach ($productStandard['values'] as $attributeCode => $values) {
            // if $context['attributes'] is defined, returns only these attributes
            if (isset($context['attributes']) && !in_array($attributeCode, $context['attributes'])) {
                unset($productStandard['values'][$attributeCode]);
            } elseif (in_array($attributeTypes[$attributeCode], [AttributeTypes::FILE, AttributeTypes::IMAGE])) {
                // returns the URI to download the file
                foreach ($values as $index => $value) {
                    $route = $this->router->generate('pim_api_media_file_download', ['code' => $value['data']]);
                    $download = new Link('download', $route);
                    $productStandard['values'][$attributeCode][$index]['_links'] = $download->toArray();
                }
            }
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
}
