<?php

namespace Pim\Behat\Decorator\Attribute;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * @param string $value
     */
    public function fill($value)
    {
        $text = $value;
        $unit = null;

        if (false !== strpos($value, ' ')) {
            list($text, $unit) = explode(' ', $value);
        }

        if (null !== $unit) {
            $item = null;

            $link = $this->spin(function () {
                return $this->find('css', 'a.select2-choice');
            }, sprintf('Could not find select2 widget inside %s', $this->element->getOuterHtml()));

            $link->click();

            $item = $this->spin(function () use ($unit) {
                return $this->getSession()
                    ->getPage()
                    ->find('css', sprintf('#select2-drop li:contains("%s")', $unit));
            }, sprintf('Could not find option "%".', $unit));

            $item->click();
        }

        $field = $this->spin(function () {
            $this->find('css', '.field-input input');
        }, 'Cannot find the metric attribute');

        $field->setValue($text);

        $this->getSession()->executeScript('$(\'.field-input input[type="text"]\').trigger(\'change\');');
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        // TODO
    }
}
