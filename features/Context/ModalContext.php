<?php

namespace Context;

use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;

use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Feature context for the modal dialog related steps
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ModalContext extends RawMinkContext implements PageObjectAwareInterface
{
    /**
     * @var \Context\Page\ModalDialog
     */
    protected $modalDialog;

    /**
     * {@inheritdoc}
     */
    public function setPageFactory(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
        $this->modalDialog = $pageFactory->createPage('ModalDialog');
    }

    /**
     * @Given /^I confirm on the grid modal window$/
     */
    public function iConfirmOnTheGridModalWindow()
    {
        $this->modalDialog->confirmOnGridModalWindow();
        $this->wait();
    }

    /**
     * @Given /^I cancel on the grid modal window$/
     */
    public function iCancelOnTheGridModalWindow()
    {
        $this->modalDialog->cancelOnGridModalWindow();
        $this->wait();
    }

    private function wait($time = 5000, $condition = 'document.readyState == "complete" && !$.active')
    {
        return $this->getMainContext()->wait($time, $condition);
    }
}
