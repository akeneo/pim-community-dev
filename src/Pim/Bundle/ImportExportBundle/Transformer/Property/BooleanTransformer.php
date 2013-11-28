<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException;

/**
 * Boolean attribute transformer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanTransformer implements PropertyTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
        if (is_bool($value)) {
            return $value;
        } elseif ($value == 0) {
            return false;
        } elseif ($value == 1) {
            return true;
        }

        throw new PropertyTransformerException('Invalid boolean');
    }
}
