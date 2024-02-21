<?php

namespace Akeneo\Pim\Enrichment\Bundle\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;

/**
 * Filter the product values according to locale codes provided in options.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueLocaleFilter implements CollectionFilterInterface, ObjectFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterObject($value, $type, array $options = [])
    {
        if (!$value instanceof ValueInterface) {
            throw new \LogicException('This filter only handles objects of type "ValueInterface"');
        }

        $localeCodes = isset($options['locales']) ? $options['locales'] : [];

        return !empty($localeCodes) &&
            $value->isLocalizable() &&
            !in_array($value->getLocaleCode(), $localeCodes);
    }

    /**
     * {@inheritdoc}
     */
    public function filterCollection($objects, $type, array $options = [])
    {
        foreach ($objects as $key => $object) {
            if ($this->filterObject($object, $type, $options)) {
                $objects->removeKey($key);
            }
        }

        return $objects;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return $object instanceof ValueInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCollection($collection, $type, array $options = [])
    {
        return $collection instanceof WriteValueCollection;
    }
}
