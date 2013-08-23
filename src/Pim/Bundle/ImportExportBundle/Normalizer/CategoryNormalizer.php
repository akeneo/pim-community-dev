<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\ProductBundle\Model\CategoryInterface;

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
     * @var array()
     */
    protected $supportedFormats = array('json', 'xml');

    /**
     * @var array()
     */
    private $results;

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
            'left'    => (string) $object->getLeft(),
            'level'   => (string) $object->getLevel(),
            'right'   => (string) $object->getRight(),
            'title'   => $this->normalizeTitle($object)
        );

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
     * Normalize the title
     *
     * @param CategoryInterface $category
     *
     * @return void
     */
    protected function normalizeTitle(CategoryInterface $category)
    {
        $titles = array();
        foreach ($category->getTranslations() as $translation) {
            $titles[$translation->getLocale()]= $translation->getTitle();
        }

        return $titles;
    }
}
