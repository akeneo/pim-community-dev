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
    /**
     * @var string
     */
    const ITEM_SEPARATOR = ',';

    /**
     * @var array
     */
    protected $supportedFormats = array('csv');

    /**
     * @var array
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
        $this->results = array();

        $this->normalizeValue($identifier = $object->getIdentifier());

        $this->normalizeFamily($object->getFamily());

        foreach ($object->getValues() as $value) {
            if ($value === $identifier) {
                continue;
            }
            $this->normalizeValue($value);
        }

        $this->normalizeCategories($object->getCategoryCodes());

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
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormats);
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
            $suffix = sprintf('-%s', $value->getLocale());
        }
        $data = $value->getData();

        if ($data instanceof \DateTime) {
            $data = $data->format('r');
        } elseif ($data instanceof \Pim\Bundle\ProductBundle\Entity\AttributeOption) {
            $data = $data->getCode();
        } elseif ($data instanceof \Doctrine\Common\Collections\Collection) {
            $result = array();
            foreach ($data as $key => $val) {
                if ($val instanceof \Pim\Bundle\ProductBundle\Entity\AttributeOption) {
                    $result[] = $val->getCode();
                } else {
                    $result[] = (string) $val;
                }
            }
            $data = join(self::ITEM_SEPARATOR, $result);
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
        $this->results['categories'] = $categories;
    }
}
