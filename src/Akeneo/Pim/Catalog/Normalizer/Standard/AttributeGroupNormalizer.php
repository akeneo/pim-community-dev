<?php

namespace Pim\Component\Catalog\Normalizer\Standard;

use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizer implements NormalizerInterface
{
    /** @var TranslationNormalizer */
    protected $translationNormalizer;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param TranslationNormalizer        $translationNormalizer
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        TranslationNormalizer $translationNormalizer,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->translationNormalizer = $translationNormalizer;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attributeGroup, $format = null, array $context = [])
    {
        return [
            'code'       => $attributeGroup->getCode(),
            'sort_order' => (int) $attributeGroup->getSortOrder(),
            'attributes' => $this->attributeRepository->getAttributeCodesByGroup($attributeGroup),
            'labels'     => $this->translationNormalizer->normalize($attributeGroup, 'standard', $context),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeGroupInterface && 'standard' === $format;
    }
}
