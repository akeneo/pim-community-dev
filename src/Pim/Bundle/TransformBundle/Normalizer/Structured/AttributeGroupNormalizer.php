<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute group normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizer implements NormalizerInterface
{
    /** @var array */
    protected $supportedFormats = ['json', 'xml'];

    /** @var NormalizerInterface */
    protected $transNormalizer;

    /** @var AttributeRepository */
    protected $attributeRepository;

    /**
     * Constructor
     *
     * @param NormalizerInterface $transNormalizer
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(
        NormalizerInterface $transNormalizer,
        AttributeRepository $attributeRepository = null
    ) {
        $this->transNormalizer = $transNormalizer;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'code'       => $object->getCode(),
            'sortOrder'  => $object->getSortOrder(),
            'attributes' => $this->normalizeAttributes($object)
        ] + $this->transNormalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeGroupInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the attributes
     *
     * @param AttributeGroupInterface $group
     *
     * @return array
     */
    protected function normalizeAttributes(AttributeGroupInterface $group)
    {
        if (null !== $this->attributeRepository) {
            return $this->attributeRepository->getAttributeCodesByGroup($group);
        }

        $attributes = [];
        foreach ($group->getAttributes() as $attribute) {
            $attributes[] = $attribute->getCode();
        }

        return $attributes;
    }
}
