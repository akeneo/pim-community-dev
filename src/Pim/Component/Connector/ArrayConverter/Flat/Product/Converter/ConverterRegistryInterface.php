<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product\Converter;

/**
 * Registry of converters
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConverterRegistryInterface
{
    /**
     * Register a copier
     *
     * @param ConverterInterface $converter
     *
     * @return ConverterRegistryInterface
     */
    public function register(ConverterInterface $converter);

    /**
     * @param string $field
     *
     * @return ConverterInterface
     */
    public function getConverter($field);
}
