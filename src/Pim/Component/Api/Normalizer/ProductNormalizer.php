<?php

namespace Pim\Component\Api\Normalizer;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
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

    /**
     * @param NormalizerInterface          $productNormalizer
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        NormalizerInterface $productNormalizer,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->productNormalizer = $productNormalizer;
        $this->attributeRepository = $attributeRepository;
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

        if (isset($context['attributes'])) {
            foreach ($productStandard['values'] as $attributeCode => $values) {
                if (!in_array($attributeCode, $context['attributes'])) {
                    unset($productStandard['values'][$attributeCode]);
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
