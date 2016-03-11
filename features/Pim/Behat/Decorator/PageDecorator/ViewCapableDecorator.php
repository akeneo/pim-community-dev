<?php

namespace Pim\Behat\Decorator\PageDecorator;

use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorators of elements to manipulate page views
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViewCapableDecorator extends ElementDecorator
{
    protected $selectors = [
        'View selector' => '#view-selector',
        'View list' => '.ui-multiselect-menu.highlight-hover',
    ];

    protected $decorators = [
        '\Pim\Behat\Decorator\ViewDecorator\ViewSelectorDecorator',
    ];

    /**
     * Finds and returns the element responsible for changing the view
     *
     * @return ElementDecorator
     */
    public function getCurrentViewSelector()
    {
        $element = $this->find('css', $this->selectors['View selector'])->getParent();

        return $this->decorate($element, $this->decorators);
    }
}
