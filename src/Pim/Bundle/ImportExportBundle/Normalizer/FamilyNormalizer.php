<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

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
     * @var array
     */
    protected $results;

    /**
     * Transforms an object into a flat array
     *
     * @param object $family
     * @param string $format
     * @param array  $context
     *
     * @return array
     */
    public function normalize($family, $format = null, array $context = array())
    {
        $this->results = array(
            'code'             => $family->getCode(),
//             'label'            => $this->normalizeLabel($family),
            'attributes'       => $this->normalizeAttributes($family),
            'attributeAsLabel' => ($family->getAttributeAsLabel()) ? $family->getAttributeAsLabel()->getCode() : '',
            'requirements'     => $this->normalizeRequirements($family),
        ) + $this->normalizeLabel($family);

        return $this->results;
    }

    /**
     * Indicates whether this normalizer can normalize the given data
     *
     * @param mixed  $data
     * @param string $format
     *
     * @return boolean
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Family && in_array($format, $this->supportedFormats);
    }

    /**
     * Normalize the label
     *
     * @param Family $family
     *
     * @return array
     */
    protected function normalizeLabel(Family $family)
    {
        $labels = array();
        foreach ($family->getTranslations() as $translation) {
            $labels[$translation->getLocale()]= $translation->getLabel();
        }

        return array('label' => $labels);
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
            $attributes[]= $attribute->getCode();
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
                $required[$channelCode]= array();
            }
            if ($requirement->isRequired()) {
                $required[$channelCode][]= $requirement->getAttribute()->getCode();
            }
        }

        return $required;
    }
}
