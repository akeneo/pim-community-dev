<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

/**
 * Attribute option normalizer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionNormalizer implements NormalizerInterface
{
    /**
     * @var array
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * Transforms an object into a flat array
     *
     * @param object $entity
     * @param string $format
     * @param array  $context
     *
     * @return array
     */
    public function normalize($entity, $format = null, array $context = array())
    {
        $results = array(
            'attribute'  => $entity->getAttribute()->getCode(),
            'code'       => $entity->getCode(),
            'is_default' => ($entity->isDefault()) ? 1 : 0,
        );
        $results = array_merge(
            $results,
            $this->getNormalizedLabelsArray($entity)
        );

        return $results;
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
        return $data instanceof AttributeOption && in_array($format, $this->supportedFormats);
    }

    /**
     * Returns an array containing the label values
     *
     * @param AttributeOption $entity
     *
     * @return array
     */
    protected function getNormalizedLabelsArray(AttributeOption $entity)
    {
        $labels = array();
        foreach ($entity->getOptionValues() as $translation) {
            $labels[$translation->getLocale()] = $translation->getValue();
        }

        return array('label' => $labels);
    }
}
