<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * A normalizer to transform a category entity into a flat array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer implements NormalizerInterface
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
     * @param object $object
     * @param string $format
     * @param array  $context
     *
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $this->results = array(
            'code'    => $object->getCode(),
            'parent'  => $object->getParent() ? $object->getParent()->getCode() : '',
            'dynamic' => (string) $object->isDynamic(),
        ) + $this->getNormalizedLabelsArray($object);

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
        return $data instanceof CategoryInterface && in_array($format, $this->supportedFormats);
    }

    /**
     * Returns an array containing the label values
     *
     * @param CategoryInterface $category
     *
     * @return array
     */
    protected function getNormalizedLabelsArray(CategoryInterface $category)
    {
        $labels = array();
        foreach ($category->getTranslations() as $translation) {
            $labels[$translation->getLocale()]= $translation->getLabel();
        }

        return array('label' => $labels);
    }
}
