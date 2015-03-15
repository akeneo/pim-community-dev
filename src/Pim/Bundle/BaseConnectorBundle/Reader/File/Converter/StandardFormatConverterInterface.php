<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File\Converter;

/**
 * Standard formatter interface, convert a format to the standard one
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface StandardFormatConverterInterface
{
    /**
     * @param mixed $data
     *
     * @return array
     */
    public function convert($data);
}