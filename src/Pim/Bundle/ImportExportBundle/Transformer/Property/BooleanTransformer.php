<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

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
    public function transform($value, array $options = [])
    {
        if ('0' === $value) {
            return false;
        } elseif ('1' === $value) {
            return true;
        }

        return $value;
    }
}
