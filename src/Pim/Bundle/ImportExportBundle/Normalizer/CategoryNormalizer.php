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
    const LOCALIZABLE_PATTERN = '{locale}:{value}';
    const ITEM_SEPARATOR      = ',';

    protected $supportedFormats = array('csv');

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
        );

        $this->normalizeTitle($object);

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
     * @param object $object
     *
     * @return void
     */
    protected function normalizeTitle($object)
    {
        $titles = $object->getTranslations()->map(
            function($translation) {
                $title = str_replace('{locale}', $translation->getLocale(), self::LOCALIZABLE_PATTERN);
                $title = str_replace('{value}', $translation->getTitle(), $title);
                return $title;
            }
        )->toArray();

        $this->results['title'] = implode(self::ITEM_SEPARATOR, $titles);
    }
}
