<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ValueConverterRegistry
{
    /** @var ValueConverterInterface[] */
    protected $converters = [];

    /**
     * {@inheritdoc}
     */
    public function register(ValueConverterInterface $converter, $priority)
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
     * @param AttributeInterface $attribute
     *
     * @return ValueConverterInterface|null
     */
    public function getConverter(AttributeInterface $attribute)
    {
        foreach ($this->converters as $converter) {
            if ($converter->supportsAttribute($attribute)) {
                return $converter;
            }
        }

        return null;
    }
}
