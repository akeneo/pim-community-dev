<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Entity\Association;

/**
 * Association normalizer
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationNormalizer implements NormalizerInterface
{
    /**
     * @var array
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * Transforms an object into a flat array
     *
     * @param object $association
     * @param string $format
     * @param array  $context
     *
     * @return array
     */
    public function normalize($association, $format = null, array $context = array())
    {
        return array(
            'code'  => $association->getCode()
        ) + $this->normalizeLabel($association);
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
        return $data instanceof Association && in_array($format, $this->supportedFormats);
    }

    /**
     * Returns an array containing the label values
     *
     * @param Association $association
     *
     * @return array
     */
    protected function normalizeLabel(Association $association)
    {
        $labels = array();
        foreach ($association->getTranslations() as $translation) {
            $labels[$translation->getLocale()] = $translation->getLabel();
        }

        return array('label' => $labels);
    }
}
