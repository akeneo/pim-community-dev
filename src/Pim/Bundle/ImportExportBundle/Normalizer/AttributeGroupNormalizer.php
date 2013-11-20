<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

/**
 * Attribute group normalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupNormalizer implements NormalizerInterface
{
    /**
     * @var array
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * @var LabelTranslationNormalizer
     */
    protected $translationNormalizer;

    /**
     * Constructor
     *
     * @param LabelTranslationNormalizer $translationNormalizer
     */
    public function __construct(LabelTranslationNormalizer $translationNormalizer)
    {
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return array(
            'code'       => $object->getCode(),
            'sortOrder'  => $object->getSortOrder(),
            'attributes' => $this->normalizeAttributes($object)
        ) + $this->translationNormalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof AttributeGroup && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the attributes
     *
     * @param AttributeGroup $group
     *
     * @return array
     */
    protected function normalizeAttributes(AttributeGroup $group)
    {
        $attributes = array();
        foreach ($group->getAttributes() as $attribute) {
            $attributes[]= $attribute->getCode();
        }

        return $attributes;
    }
}
