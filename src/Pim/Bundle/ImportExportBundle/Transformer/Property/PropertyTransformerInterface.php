<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException;

/**
 * Interface for transformer classes
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PropertyTransformerInterface
{
    /**
     * Returns transformed value
     *
     * @param string|array $value
     * @param array        $options
     *
     * @throws PropertyTransformerException
     * @return mixed
     */
    public function transform($value, array $options = []);
}
