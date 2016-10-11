<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Behat\Context;

use Akeneo\ActivityManager\Behat\Context;
use Akeneo\ActivityManager\Behat\ContextInterface;
use Context\Spin\SpinCapableTrait;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ViewSelectorContext extends Context implements ContextInterface
{
    use SpinCapableTrait;

    /**
     * @Given /^I open the view selector$/
     */
    public function iOpenTheViewSelector()
    {
        $this->getCurrentPage()->getViewSelector()->click();
    }

    /**
     * @Then /^I click on "Create ([^"]*)" action in the dropdown$/
     *
     * @param string $action
     */
    public function iClickOnCreateAction($action)
    {
        $this->getCurrentPage()
            ->getSelectViewActionDropdown()
            ->open()
            ->chooseAction($action);
    }
}
