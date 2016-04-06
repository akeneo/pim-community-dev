<?php

namespace Pim\Behat\Decorator\Attribute;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SwitchDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * @param string $value
     */
    public function fill($value)
    {
        // TODO
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        // TODO
    }

    /**
     * @return NodeElement
     */
    protected function getInput()
    {
        // TODO
    }
}
