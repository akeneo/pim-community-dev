<?php

namespace Context;

use Behat\Behat\Context\Step;
use Behat\MinkExtension\Context\RawMinkContext;
use Context\Page\Asset\Edit;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Overrided context
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseAssetContext extends RawMinkContext
{
    /**
     * @Then /^I delete the reference file$/
     * @Then /^I delete the (channel) variation file$/
     */
    public function iDeleteTheReferenceFile()
    {
        $this->getCurrentPage()->deleteReference();
    }

    /**
     * @Then /^I can upload a reference file$/
     */
    public function iCanUploadAssetFile()
    {
        $this->getCurrentPage()->deleteReference();
    }

    /**
     * @return Edit|Page
     */
    protected function getCurrentPage()
    {
        return $this->getMainContext()->getSubcontext('navigation')->getCurrentPage();
    }
}
