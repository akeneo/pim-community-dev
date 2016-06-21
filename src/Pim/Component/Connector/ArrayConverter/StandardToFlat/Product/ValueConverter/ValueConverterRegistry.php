<?php

namespace Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValueConverterRegistry
{
    /** @var AbstractValueConverter[] */
    protected $converters = [];

    /**
     * {@inheritdoc}
     */
    public function register(AbstractValueConverter $converter, $priority)
    {
        $priority = (int)$priority;
        if (!isset($this->converters[$priority])) {
            $this->converters[$priority] = $converter;
        } else {
            $this->register($converter, ++$priority);
        }

        ksort($this->converters);

        return $this;
    }

    /**
     * @param $attributeType
     *
     * @return null|AbstractValueConverter
     */
    public function getConverter($attributeType)
    {
        foreach ($this->converters as $converter) {
            if ($converter->supportsAttribute($attributeType)) {
                return $converter;
            }
        }

        return null;
    }
}
