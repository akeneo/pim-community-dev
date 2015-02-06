<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Structured;

use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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
     * @var array $supportedFormats
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * @var TranslationNormalizer $transNormalizer
     */
    protected $transNormalizer;

    /**
     * Constructor
     *
     * @param TranslationNormalizer $transNormalizer
     */
    public function __construct(TranslationNormalizer $transNormalizer)
    {
        $this->transNormalizer = $transNormalizer;
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
        ) + $this->transNormalizer->normalize($object, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FamilyInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the attributes
     *
     * @param FamilyInterface $family
     *
     * @return array
     */
    protected function normalizeAttributes(FamilyInterface $family)
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
     * @param FamilyInterface $family
     *
     * @return array
     */
    protected function normalizeRequirements(FamilyInterface $family)
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
