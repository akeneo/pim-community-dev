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
     *
     * @param null $channel
     *
     * @throws ElementNotFoundException
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
     *
     * @param $channel
     *
     * @throws ElementNotFoundException
     */
    public function iGenerateVariationFile($channel)
    {
        $this->getCurrentPage()->generateVariationFile($channel);
    }

    /**
     * @Then /^I should see the reference upload zone$/
     * @Then /^I should see the (\S+) variation upload zone$/
     *
     * @param null $channel
     *
     * @throws ElementNotFoundException
     */
    public function iShouldSeeFileUploadZone($channel = null)
    {
        if (null === $channel) {
            $this->getCurrentPage()->findReferenceUploadZone();
        } else {
            $this->getCurrentPage()->findVariationUploadZone($channel);
        }
    }

    /**
     * @Then /^I should( not)? be able to generate (\S+) from reference$/
     *
     * @param bool   $not
     * @param string $channel
     *
     * @throws ElementNotFoundException
     */
    public function iCanGenerateChannel($not = false, $channel)
    {
        try {
            $this->getCurrentPage()->findVariationGenerateZone($channel);
        } catch (ElementNotFoundException $e) {
            if (!$not) {
                throw $e;
            }
        }
    }

    /**
     * @Given /^I upload the reference file (\S+)$/
     *
     * @param $file
     */
    public function iUploadTheReferenceFile($file)
    {
        $this->iUploadTheAssetFile($file);
    }

    /**
     * @Given /^I upload the (\S+) variation file (\S+)$/
     *
     * @param string $channel
     * @param mixed  $file
     */
    public function iUploadTheVariationFile($channel, $file)
    {
        $this->iUploadTheAssetFile($file, $channel);
    }

    /**
     * @param mixed|null  $file
     * @param string|null $channel
     *
     * @throws ElementNotFoundException
     */
    protected function iUploadTheAssetFile($file = null, $channel = null)
    {
        if ($this->getMinkParameter('files_path')) {
            $fullPath = rtrim(realpath($this->getMinkParameter('files_path')),
                    DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file;
            if (is_file($fullPath)) {
                $file = $fullPath;
            }
        }

        if (null === $channel) {
            $uploadZone = $this->getCurrentPage()->findReferenceUploadZone();
        } else {
            $uploadZone = $this->getCurrentPage()->findVariationUploadZone($channel);
        }

        $field = $uploadZone->find('css', 'input[type="file"]');
        $field->attachFile($fullPath);
    }

    /**
     * @return Edit|Page
     */
    protected function getCurrentPage()
    {
        return $this->getMainContext()->getSubcontext('navigation')->getCurrentPage();
    }
}
