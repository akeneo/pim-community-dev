<?php

namespace Pim\Bundle\TransformBundle\Transformer\Property;

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
        $value = trim($value);
        if (empty($value)) {
            return null;
        } elseif ('0' === $value) {
            return false;
        } elseif ('1' === $value) {
            return true;
        }

        return $value;
    }
}
