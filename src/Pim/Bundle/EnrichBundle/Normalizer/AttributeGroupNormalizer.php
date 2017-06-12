<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizer implements NormalizerInterface
{
    /** @var array $supportedFormats */
    protected $supportedFormats = ['internal_api'];

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var EntityRepository */
    protected $attributeRepository;

    /**
     * @param NormalizerInterface $normalizer
     * @param EntityRepository    $attributeRepository
     */
    public function __construct(NormalizerInterface $normalizer, EntityRepository $attributeRepository)
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

        $attributes = $this->attributeRepository->findBy(
            ['code' => $standardAttributeGroup['attributes']]
        );
        $sortOrder = [];
        foreach ($attributes as $attribute) {
            $sortOrder[$attribute->getCode()] = $attribute->getSortOrder();
        }
        $standardAttributeGroup['attributes_sort_order'] = $sortOrder;
        $standardAttributeGroup['meta'] = [
            'id' => $attributeGroup->getId()
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
}
