<?php

namespace Context;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ViewSelectorContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @Given /^I open the view selector$/
     */
    public function iOpenTheViewSelector()
    {
        $this->spin(function () {
            $this->getCurrentPage()->getViewSelector()->open();

            return true;
        }, 'Cannot open the view selector.');
    }

    /**
     * @Given /^I filter view selector with name "([^"]*)"$/
     *
     * @param string $name
     */
    public function iFilterViewSelectorWithName($name)
    {
        $this->getCurrentPage()->getViewSelector()->search($name);
    }

    /**
     * @Given /^I should be on the view "([^"]*)"$/
     *
     * @param string $viewName
     *
     * @throws \UnexpectedValueException
     */
    public function iShouldBeOnTheView($viewName)
    {
        $this->spin(function () {
            $loadingMask = $this->getCurrentPage()->find('css', '.hash-loading-mask .loading-mask');

            return (null !== $loadingMask && !$loadingMask->isVisible());
        }, 'Grid loading mask is still visible.');

        $currentViewName = $this->getCurrentPage()->getViewSelector()->getCurrentValue();

        if ($currentViewName !== $viewName) {
            throw new \UnexpectedValueException(
                sprintf(
                    'Expecting to be on the view "%s", but current view is "%s".',
                    $viewName,
                    $currentViewName
                )
            );
        }
    }
}
