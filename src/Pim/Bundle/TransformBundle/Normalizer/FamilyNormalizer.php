<?php

namespace Pim\Bundle\TransformBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Family normalizer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyNormalizer implements NormalizerInterface
{
    /**
     * @var array
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * @var TranslationNormalizer
     */
    protected $translationNormalizer;

    /**
     * Constructor
     *
     * @param TranslationNormalizer $translationNormalizer
     */
    public function __construct(TranslationNormalizer $translationNormalizer)
    {
        $this->translationNormalizer = $translationNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return array(
            'code'             => $object->getCode(),
            'attributes'       => $this->normalizeAttributes($object),
            'attributeAsLabel' => ($object->getAttributeAsLabel()) ? $object->getAttributeAsLabel()->getCode() : '',
            'requirements'     => $this->normalizeRequirements($object),
        ) + $this->translationNormalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Family && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the attributes
     *
     * @param Family $family
     *
     * @return array
     */
    protected function normalizeAttributes(Family $family)
    {
        $attributes = array();
        foreach ($family->getAttributes() as $attribute) {
            $attributes[] = $attribute->getCode();
        }

        return $attributes;
    }

    /**
     * Normalize the requirements
     *
     * @param Family $family
     *
     * @return array
     */
    protected function normalizeRequirements(Family $family)
    {
        $required = array();
        foreach ($family->getAttributeRequirements() as $requirement) {
            $channelCode = $requirement->getChannel()->getCode();
            if (!isset($required[$channelCode])) {
                $required[$channelCode] = array();
            }
            if ($requirement->isRequired()) {
                $required[$channelCode][] = $requirement->getAttribute()->getCode();
            }
        }

        return $required;
    }
}
