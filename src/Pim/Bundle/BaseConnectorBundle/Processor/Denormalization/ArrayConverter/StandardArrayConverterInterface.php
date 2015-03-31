<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization\ArrayConverter;

use Pim\Bundle\BaseConnectorBundle\Exception\ArrayConversionException;

/**
 * Standard converter interface, convert a format to the standard one
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface StandardArrayConverterInterface
{
    /**
     * @param array $data    data to convert
     * @param array $options options used to convert
     *
     * @return array
     *
     * @throws ArrayConversionException
     */
    public function convert(array $data);
}
