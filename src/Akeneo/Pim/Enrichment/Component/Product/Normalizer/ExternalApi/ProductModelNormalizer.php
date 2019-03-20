<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Bundle\Sql\GetMediaAttributeCodes;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $productModelNormalizer;

    /** @var GetMediaAttributeCodes */
    protected $getMediaAttributeCodes;

    /** @var RouterInterface */
    protected $router;

    /**
     * @param NormalizerInterface $productModelNormalizer
     * @param GetMediaAttributeCodes $getMediaAttributeCodes
     * @param RouterInterface $router
     */
    public function __construct(
        NormalizerInterface $productModelNormalizer,
        GetMediaAttributeCodes $getMediaAttributeCodes,
        RouterInterface $router
    ) {
        $this->productModelNormalizer = $productModelNormalizer;
        $this->getMediaAttributeCodes = $getMediaAttributeCodes;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($productModel, $format = null, array $context = []): array
    {
        $productModelStandard = $this->productModelNormalizer->normalize($productModel, 'standard', $context);

        $mediaAttributeCodes = $this->getMediaAttributeCodes->execute();
        foreach ($productModelStandard['values'] as $attributeCode => $values) {
            // if $context['attributes'] is defined, returns only these attributes
            if (isset($context['attributes']) && !in_array($attributeCode, $context['attributes'])) {
                unset($productModelStandard['values'][$attributeCode]);
            } elseif (in_array($attributeCode, $mediaAttributeCodes)) {
                $productModelStandard['values'][$attributeCode] = $this->addDownloadLink($values);
            }
        }

        if (empty($productModelStandard['values'])) {
            $productModelStandard['values'] = (object) $productModelStandard['values'];
        }

        return $productModelStandard;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductModelInterface && 'external_api' === $format;
    }

    /**
     * @param array $values
     *
     * @return array
     */
    protected function addDownloadLink(array $values): array
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
