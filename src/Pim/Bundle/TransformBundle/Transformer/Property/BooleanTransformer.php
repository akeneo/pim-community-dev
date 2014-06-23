<?php

namespace Pim\Bundle\TransformBundle\Transformer\Property;

use Pim\Bundle\TransformBundle\Exception\PropertyTransformerException;

/**
 * Boolean attribute transformer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanTransformer implements PropertyTransformerInterface
{
    /** @var mixed */
    protected $default;

    /**
     * @param mixed $default
     */
    public function __construct($default = null)
    {
        $this->default = $default;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value) && '' === $value = trim($value)) {
            return $this->default;
        }

        if (in_array($value, ['0', 'false', 'no'])) {
            return false;
        }

        if (in_array($value, ['1', 'true', 'yes'])) {
            return true;
        }

        throw new PropertyTransformerException(
            'Cannot transform "%value%" into boolean',
            [
                '%value%' => is_object($value) ? get_class($value) : gettype($value) . '( ' . $value . ' )',
            ]
        );
    }
}
