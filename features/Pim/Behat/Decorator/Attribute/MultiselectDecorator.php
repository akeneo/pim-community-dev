<?php

namespace Pim\Behat\Decorator\Attribute;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Helper\StringHelper;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MultiselectDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * @param string $values
     */
    public function fill($values)
    {
        $field = $this->spin(function () {
            return $this->find('css', '.form-field');
        }, 'Cannot find form field');

        // clear multi select first
        $fieldClasses = $field->getAttribute('class');
        if (preg_match('/akeneo-multi-select(-reference-data)?-field/', $fieldClasses, $matches)) {
            $select2Selector = sprintf('.%s div.field-input > input', $matches[0]);
            $script          = sprintf('$("%s").select2("val", "");$("%1$s").trigger("change");', $select2Selector);
            $this->getSession()->executeScript($script);
        }

        $link = $this->spin(function () {
            return $this->find('css', 'ul.select2-choices');
        }, sprintf('Could not find select2 widget inside %s', $this->element->getOuterHtml()));

        foreach (StringHelper::listToArray($values) as $value) {
            $link->click();
            $item = $this->spin(function () use ($value) {
                return $this->getSession()
                    ->getPage()
                    ->find(
                        'css',
                        sprintf('.select2-result:not(.select2-selected) .select2-result-label:contains("%s")', $value)
                    );
            }, sprintf('Could not find select2 item with value %s inside %s', $value, $link->getHtml()));

            // Select the value in the displayed dropdown
            $item->click();
        }

        $this->getSession()->executeScript(
            '$(\'.field-input input.select-field\').trigger(\'change\');'
        );
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        // TODO
    }
}
