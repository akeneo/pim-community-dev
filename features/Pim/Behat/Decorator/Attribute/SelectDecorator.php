<?php

namespace Pim\Behat\Decorator\Attribute;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SelectDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * @param string $value
     */
    public function fill($value)
    {
        if ('' === $value || null === $value) {
            $emptyLink = $this->spin(function () {
                return $this->find('css', '.select2-search-choice-close');
            }, 'Cannot find the select2 choice close');

            $emptyLink->click();

            $this->getSession()->executeScript(
                '$(\'.field-input input[type="hidden"].select-field\').trigger(\'change\');'
            );

            return;
        }

        $link = $this->spin(function () {
            return $this->find('css', 'a.select2-choice');
        }, sprintf('Could not find select2 widget inside %s', $this->getParent()->getHtml()));


        $link->click();

        $item = $this->spin(function () use ($link, $value) {
            return $this->getSession()
                ->getPage()
                ->find('css', sprintf('.select2-results li:contains("%s")', $value));
        }, sprintf('Cannot find the select2 result with value "%s"', $value));

        $item->click();

        $this->getSession()->executeScript(
            '$(\'.field-input input[type="hidden"].select-field\').trigger(\'change\');'
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
