<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\ProductBundle\Model\ProductInterface;
use Pim\Bundle\ProductBundle\Entity\Family;

/**
 * A normalizer to transform a product entity into a flat array
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatProductNormalizer implements NormalizerInterface
{
    private $results;

    /**
     * Transforms a product into a flat array
     *
     * @param ProductInterface $object
     * @param string           $format
     * @param array            $context
     *
     * @return array
     */
    public function normalize(ProductInterface $object, $format = null, array $context = array())
    {
        $this->results = array();

        $this->normalizeValue($identifier = $object->getIdentifier());

        $this->normalizeFamily($object->getFamily());

        foreach ($object->getValues() as $value) {
            if ($value === $identifier) {
                continue;
            }
            $this->normalizeValue($value);
        }

        $this->normalizeCategories($object->getCategoryTitlesAsString());

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
        return $data instanceof ProductInterface && 'csv' === $format;
    }

    /**
     * Normalizes a value
     *
     * @param mixed $value
     */
    private function normalizeValue($value)
    {
        $suffix = '';
        if ($value->getAttribute()->getTranslatable()) {
            $suffix = sprintf('_%s', $value->getLocale());
        }
        $data = $value->getData();

        if ($data instanceof \DateTime) {
            $data = $data->format('r');
        } elseif ($data instanceof \Doctrine\Common\Collections\Collection) {
            $data = '"' . join(',', $data->toArray()) . '"';
        }

        $this->results[$value->getAttribute()->getCode().$suffix] = (string) $data;
    }

    /**
     * Normalizes a family
     *
     * @param Family $family
     */
    private function normalizeFamily(Family $family = null)
    {
        $this->results['family'] = $family ? $family->getCode() : '';
    }

    /**
     * Normalizes categories
     *
     * @param string $categories
     */
    private function normalizeCategories($categories = '')
    {
        $this->results['categories'] = sprintf('"%s"', $categories);
    }
}
