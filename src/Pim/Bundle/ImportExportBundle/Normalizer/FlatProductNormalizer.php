<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Family;

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
    protected $results;

    /**
     * Fields to export if needed
     * @var array
     */
    protected $fields = array();

    protected function initializeFields($fields)
    {
        $this->fields = array_fill_keys($fields, '');
    }

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
        // initialize context
        $scope  = isset($context['scope']) && is_string($context['scope']) ? $context['scope'] : null;
        if (isset($context['fields']) && is_array($context['fields'])) {
            $this->initializeFields($context['fields']);
        }

        $this->results = $this->fields;

        $identifier = $object->getIdentifier();
        $this->normalizeValue($identifier);

        $this->normalizeFamily($object->getFamily());

        foreach ($object->getValues() as $value) {
            if ($value === $identifier) {
                continue;
            } elseif ($value->getScope() !== null && $scope !== null) {
                if ($value->getScope() !== $scope) {
                    continue;
                }
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
    protected function normalizeValue($value)
    {

        if (empty($this->fields) || isset($this->fields[$value->getAttribute()->getCode()])) {
            $data = $value->getData();

            if ($data instanceof \DateTime) {
                $data = $data->format('r');
            } elseif ($data instanceof \Pim\Bundle\CatalogBundle\Entity\AttributeOption) {
                $data = $data->getCode();
            } elseif ($data instanceof \Doctrine\Common\Collections\Collection) {
                $result = array();
                foreach ($data as $item) {
                    if ($item instanceof \Pim\Bundle\CatalogBundle\Entity\AttributeOption) {
                        $result[] = $item->getCode();
                    } else {
                        $result[] = (string) $item;
                    }
                }
                $data = join(self::ITEM_SEPARATOR, $result);
            }
        } else {
            $data = '';
        }

        $this->results[$this->getFieldValue($value)] = (string) $data;
    }

    /**
     * @param ProductValueInterface $value
     * @return string
     */
    protected function getFieldValue($value)
    {
        $suffix = '';
//         if ($value->getAttribute()->getTranslatable()) {
//             $suffix = $suffix = sprintf('-%s', $value->getLocale());
//         }

        return $value->getAttribute()->getCode() . $suffix;
    }

    /**
     * Normalizes a family
     *
     * @param Family $family
     */
    protected function normalizeFamily(Family $family = null)
    {
//         if (empty($this->fields) || isset($this->fields['family'])) {
            $this->results['family'] = $family ? $family->getCode() : '';
//         }
    }

    /**
     * Normalizes categories
     *
     * @param string $categories
     */
    protected function normalizeCategories($categories = '')
    {
        if (empty($this->fields) || isset($this->fields['categories'])) {
            $this->results['categories'] = $categories;
        }
    }
}
