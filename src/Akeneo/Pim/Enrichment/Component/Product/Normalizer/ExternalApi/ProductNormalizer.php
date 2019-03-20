<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\Sql\GetMediaAttributeCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Api\Hal\Link;
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

    /** @var LruArrayAttributeRepository */
    protected $attributeRepository;

    /** @var GetMediaAttributeCodes */
    protected $getMediaAttributeCodes;

    /** @var RouterInterface */
    protected $router;

    /**
     * @param NormalizerInterface $productNormalizer
     * @param LruArrayAttributeRepository $attributeRepository
     * @param GetMediaAttributeCodes $getMediaAttributeCodes
     * @param RouterInterface $router
     */
    public function __construct(
        NormalizerInterface $productNormalizer,
        LruArrayAttributeRepository $attributeRepository,
        GetMediaAttributeCodes $getMediaAttributeCodes,
        RouterInterface $router
    ) {
        $this->productNormalizer = $productNormalizer;
        $this->attributeRepository = $attributeRepository;
        $this->getMediaAttributeCodes = $getMediaAttributeCodes;
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

        $mediaAttributeCodes = $this->getMediaAttributeCodes->execute();
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
