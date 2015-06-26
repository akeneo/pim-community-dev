<?php

namespace Context;

use Behat\Behat\Context\Step;
use Behat\Mink\Exception\ElementNotFoundException;
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
     * @Then /^I delete the (\S+) variation file$/
     */
    public function iDeleteTheFile($channel = null)
    {
        if (null === $channel) {
            $this->getCurrentPage()->deleteReferenceFile();
        } else {
            $this->getCurrentPage()->deleteVariationFile($channel);
        }
    }

    /**
     * @Then /^I reset variations files$/
     */
    public function iResetVariationsFiles()
    {
        $this->getCurrentPage()->resetVariationsFiles();
    }

    /**
     * @Then /^I generate (\S+) variation from reference$/
     */
    public function iGenerateVariationFile($channel)
    {
        $this->getCurrentPage()->generateVariationFile($channel);
    }

    /**
     * @Then /^I should see the reference upload zone$/
     * @Then /^I should see the (\S+) variation upload zone$/
     */
    public function iCanUploadAssetFile($channel = null)
    {
        if (null === $channel) {
            $this->getCurrentPage()->findReferenceUploadZone();
        }
    }

    /**
     * @Then /^I should( not)? be able to generate (\S+) from reference$/
     *
     * @param bool $not
     * @param      $channel
     *
     * @throws ElementNotFoundException
     */
    public function iCanGenerateChannel($not = false, $channel)
    {
        try {
            $this->getCurrentPage()->findVariationGenerateZone($channel);
        } catch (ElementNotFoundException $e) {
            if ($not) {
                // do nothing
            } else {
                throw $e;
            }
        }
    }

    /**
     * @return Edit|Page
     */
    protected function getCurrentPage()
    {
        return $this->getMainContext()->getSubcontext('navigation')->getCurrentPage();
    }
}
