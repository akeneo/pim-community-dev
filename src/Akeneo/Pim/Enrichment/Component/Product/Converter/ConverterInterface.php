<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Converter;

/**
 * The standard format is not exactly formatted the same way as the format expected by the frontend.
 * Therefore, it converts frontend format to expected backend format and vice-versa, in order to make them communicate.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConverterInterface
{
    /**
     * Convert data
     *
     * @param array $data
     *
     * @return array
     */
    public function convert(array $data);
}
