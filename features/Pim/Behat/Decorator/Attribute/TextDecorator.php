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
class TextDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * @param string $value
     */
    public function fill($value)
    {
        $input = $this->getInput();
        $input->setValue($value);

        $this->getSession()->executeScript('$(\'.field-input input[type="text"]\').trigger(\'change\');');
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->getInput()->getValue();
    }

    /**
     * @return NodeElement
     */
    protected function getInput()
    {
        $input = $this->spin(function () {
            return $this->find('css', 'div.field-input input');
        }, 'Cannot find the text attribute');

        return $input;
    }
}
