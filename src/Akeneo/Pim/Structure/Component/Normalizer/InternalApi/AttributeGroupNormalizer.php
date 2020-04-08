<?php

namespace Akeneo\Pim\Structure\Component\Normalizer\InternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizer implements NormalizerInterface
{
    private const MAX_ATTRIBUTES_SHOWN = 500;

    /** @var array $supportedFormats */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ObjectRepository */
    protected $attributeRepository;

    /**
     * @param NormalizerInterface $normalizer
     * @param ObjectRepository    $attributeRepository
     */
    public function __construct(NormalizerInterface $normalizer, ObjectRepository $attributeRepository)
    {
        $this->normalizer          = $normalizer;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($attributeGroup, $format = null, array $context = [])
    {
        $standardAttributeGroup = $this->normalizer->normalize($attributeGroup, 'standard', $context);
        $totalCount = \count($standardAttributeGroup['attributes']);
        $standardAttributeGroup = $this->showOnlyMaximumAttributes($standardAttributeGroup);

        $attributes = $this->attributeRepository->findBy(['code' => $standardAttributeGroup['attributes']]);
        $count = \count($attributes);

        $sortOrder = [];
        foreach ($attributes as $attribute) {
            $sortOrder[$attribute->getCode()] = $attribute->getSortOrder();
        }
        $standardAttributeGroup['attributes_sort_order'] = $sortOrder;
        $standardAttributeGroup['meta'] = [
            'id' => $attributeGroup->getId(),
            'attribute_count' => $count,
            'total_attribute_count' => $totalCount
        ];

        return $standardAttributeGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeGroupInterface && in_array($format, $this->supportedFormats);
    }

    private function showOnlyMaximumAttributes(array $standardAttributeGroup): array
    {
        $standardAttributeGroup['attributes'] = array_splice($standardAttributeGroup['attributes'], 0, self::MAX_ATTRIBUTES_SHOWN);

        return $standardAttributeGroup;
    }
}
