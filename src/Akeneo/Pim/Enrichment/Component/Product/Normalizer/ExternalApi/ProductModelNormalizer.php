<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Repository\ExternalApi\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    protected $productModelNormalizer;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var RouterInterface */
    protected $router;

    /**
     * @param NormalizerInterface          $productModelNormalizer
     * @param AttributeRepositoryInterface $attributeRepository
     * @param RouterInterface              $router
     */
    public function __construct(
        NormalizerInterface $productModelNormalizer,
        AttributeRepositoryInterface $attributeRepository,
        RouterInterface $router
    ) {
        $this->productModelNormalizer = $productModelNormalizer;
        $this->attributeRepository = $attributeRepository;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($productModel, $format = null, array $context = []): array
    {
        $productModelStandard = $this->productModelNormalizer->normalize($productModel, 'standard', $context);
        $productModelStandard['family'] = $productModel->getFamily()->getCode();

        $mediaAttributeCodes = $this->attributeRepository->getMediaAttributeCodes();
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

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
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
