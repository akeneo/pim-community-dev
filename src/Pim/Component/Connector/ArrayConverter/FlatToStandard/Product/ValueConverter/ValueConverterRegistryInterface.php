<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter;

/**
 * Registry of converters.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ValueConverterRegistryInterface
{
    /**
     * Register a converter.
     *
     * @param ValueConverterInterface $converter
     * @param int                     $priority FIFO order : converter with lower priority is inspected first.
     *
     * @return ValueConverterRegistry
     */
    public function register(ValueConverterInterface $converter, $priority);

    /**
     * @param string $attributeType
     *
     * @return ValueConverterInterface|null
     */
    public function getConverter($attributeType);
}
