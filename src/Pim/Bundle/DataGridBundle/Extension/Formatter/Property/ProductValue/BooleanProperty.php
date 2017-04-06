<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue;

/**
 * Boolean field property, able to render boolean attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanProperty extends TwigProperty
{
    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        return $this->getTemplate()->render(['value' => $value['data']]);
    }
}
