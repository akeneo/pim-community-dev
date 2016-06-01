<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter;

/**
 * Registry of converters.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueConverterRegistry implements ValueConverterRegistryInterface
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
     * {@inheritdoc}
     */
    public function getConverter($attributeType)
    {
        foreach ($this->converters as $converter) {
            if ($converter->supportsField($attributeType)) {
                return $converter;
            }
        }

        return null;
    }
}
