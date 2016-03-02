<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
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

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * Constructor
     *
     * @param NormalizerInterface          $transNormalizer
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        NormalizerInterface $transNormalizer,
        AttributeRepositoryInterface $attributeRepository
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
            'attributes' => $this->attributeRepository->getAttributeCodesByGroup($object)
        ] + $this->transNormalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeGroupInterface && in_array($format, $this->supportedFormats);
    }
}
