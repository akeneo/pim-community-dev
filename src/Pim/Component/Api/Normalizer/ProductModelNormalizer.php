<?php

declare(strict_types=1);

namespace Pim\Component\Api\Normalizer;

use Pim\Component\Api\Hal\Link;
use Pim\Component\Api\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Elodie Raposo <elodie.raposo@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNormalizer implements NormalizerInterface
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
    public function normalize($productModel, $format = null, array $context = [])
    {
        $productModelStandard = $this->productModelNormalizer->normalize($productModel, 'standard', $context);
        $identifier = $this->attributeRepository->getIdentifierCode();

        if (isset($productModelStandard['values'][$identifier])) {
            unset($productModelStandard['values'][$identifier]);
        }

        foreach ($productModelStandard['values'] as $attributeCode => $values) {
            // if $context['attributes'] is defined, returns only these attributes
            if (isset($context['attributes']) && !in_array($attributeCode, $context['attributes'])) {
                unset($productModelStandard['values'][$attributeCode]);
            }
        }

        if (empty($productModelStandard['values'])) {
            $productModelStandard['values'] = (object) $productModelStandard['values'];
        }

        if (empty($productModelStandard['associations'])) {
            $productModelStandard['associations'] = (object) $productModelStandard['associations'];
        }

        return $productModelStandard;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductModelInterface && 'external_api' === $format;
    }
}
