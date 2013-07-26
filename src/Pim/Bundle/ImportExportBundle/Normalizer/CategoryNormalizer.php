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
        $results = array();

        $results['code']    = $object->getCode();
        $results['parent']  = $object->getParent() ? $object->getParent()->getCode() : '';
        $results['dynamic'] = (string) $object->isDynamic();
        $results['left']    = (string) $object->getLeft();
        $results['level']   = (string) $object->getLevel();
        $results['right']   = (string) $object->getRight();

        $titles = array();
        foreach ($object->getTranslations() as $translation) {
            $titles[] = sprintf('%s:%s', $translation->getLocale(), $translation->getTitle());
        }
        $results['title'] = implode(',', $titles);

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
        return $data instanceof CategoryInterface && 'csv' === $format;
    }
}
