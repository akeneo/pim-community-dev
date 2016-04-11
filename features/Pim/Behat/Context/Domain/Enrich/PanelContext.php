<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Context\Spin\TimeoutException;
use Pim\Behat\Context\PimContext;

class PanelContext extends PimContext
{
    /**
     * @param string $panel
     *
     * @Given /^I open the "([^"]*)" panel$/
     */
    public function iOpenThePanel($panel)
    {
        $this->getCurrentPage()->getElement('Panel sidebar')->openPanel($panel);
        $this->wait();
    }

    /**
     * @param string $panel
     *
     * @Given /^I close the "([^"]*)" panel$/
     */
    public function iCloseThePanel($panel)
    {
        $this->getCurrentPage()->getElement('Panel sidebar')->closePanel($panel);
        $this->wait();
    }
}
