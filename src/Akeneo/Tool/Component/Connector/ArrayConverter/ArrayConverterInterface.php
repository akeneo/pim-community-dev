<?php

namespace Akeneo\Tool\Component\Connector\ArrayConverter;

use Akeneo\Tool\Component\Connector\Exception\ArrayConversionException;

/**
 * Array converter interface, convert an array format to another one
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ArrayConverterInterface
{
    /**
     * @param array $item    data to convert
     * @param array $options options to use to convert
     *
     * @throws ArrayConversionException
     *
     * @return array
     */
    public function convert(array $item, array $options = []);
}
