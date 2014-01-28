<?php

namespace Pim\Bundle\TransformBundle\Transformer\Property;

use Pim\Bundle\TransformBundle\Exception\PropertyTransformerException;

/**
 * Date attribute transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTransformer implements PropertyTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value, array $options = array())
    {
        $value = trim($value);

        if (empty($value)) {
            return null;
        }

        try {
            return new \DateTime($value);
        } catch (\Exception $ex) {
            throw new PropertyTransformerException('Invalid date');
        }
    }
}
