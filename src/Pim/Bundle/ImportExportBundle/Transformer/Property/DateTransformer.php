<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Exception\InvalidValueException;

/**
 * Date attribute transformer
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTransformer implements PropertyTransformerInterface
{
    public function transform($value, array $options = array())
    {
        try {
            return new \DateTime($value);
        } catch (\Exception $ex) {
            throw new InvalidValueException('Invalid date');
        }
    }

}
