<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product\Converter;

/**
 * Registry of converters
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConverterRegistry implements ConverterRegistryInterface
{
    /** @var array */
    protected $converters = [];

    /**
     * {@inheritdoc}
     */
    public function register(ConverterInterface $converter)
    {
        if ($converter instanceof ConverterInterface) {
            $this->converters[] = $converter;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConverter($field)
    {
        foreach ($this->converters as $converter) {
            if ($converter->supportsField($field)) {
                return $converter;
            }
        }

        return null;
    }
}
