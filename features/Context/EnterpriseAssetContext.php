<?php

namespace Context;

use Behat\Behat\Context\Step;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\RawMinkContext;
use Context\Page\Asset\Edit;
use PimEnterprise\Bundle\ProductAssetBundle\Doctrine\Common\Saver\AssetVariationSaver;
use PimEnterprise\Component\ProductAsset\Updater\FilesUpdaterInterface;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Symfony\Component\DependencyInjection\Container;

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
     * @param null|string $channel
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
     * @Given /^I switch localizable button to (\yes|no|Yes|No)$/
     */
    public function iSwitchLocalizableButtonTo($isLocalizable)
    {
        if ('yes' === strtolower($isLocalizable)) {
            $isLocalizable = 'on';
        } else {
            $isLocalizable = 'off';
        }

        $this->getCurrentPage()->changeLocalizableSwitch($isLocalizable);
    }

    /**
     * @Given /^I fill the code with (\S+)(| and wait for validation)$/
     */
    public function iFillTheCodeWith($value, $wait)
    {
        $dialog = $this->getCurrentPage()->getDialog();
        $code   = $dialog->findField('Code');
        $code->setValue($value);

        if (!empty($wait)) {
            $iconContainer = $code->getParent()->find('css', '.icons-container');
            $this->getMainContext()->spin(function () use ($iconContainer) {
                $tooltip = $iconContainer->find('css', 'i.validation-tooltip');
                return $tooltip ? true : false;
            });
        }
    }

    /**
     * @Then /^removing "([^"]+)" permissions should hide "([^"]+)" button on "([^"]+)" page$/
     */
    public function removingPermissionsShouldHideButtonOnPage($permission, $button, $page)
    {
        $steps = [];

        $steps[] = new Step\Then('I am on the "Administrator" role page');
        $steps[] = new Step\Then(sprintf('I remove rights to %s', $permission));
        $steps[] = new Step\Then('I save the role');
        $steps[] = new Step\Then(sprintf('I am on the %s page', $page));
        $steps[] = new Step\Then(sprintf('I should not see the "%s" button', $button));
        $steps[] = new Step\Then('I reset the "Administrator" rights');

        return $steps;
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

    /**
     * @Then /^I delete the (\S+) variation for channel (\S+) and locale "(\S*)"$/
     *
     * @param $assetCode
     * @param $localeCode
     * @param $channelCode
     */
    public function iDeleteVariation($assetCode, $channelCode, $localeCode = null)
    {
        $asset = $this->getFixturesContext()->getAsset($assetCode);

        $locale  = $this->getFixturesContext()->getLocaleRepository()->findOneBy(['code' => $localeCode]);
        $channel = $this->getFixturesContext()->getChannelRepository()->findOneBy(['code' => $channelCode]);

        if ($localeCode) {
            $reference = $asset->getReference($locale);
        } else {
            $reference = $asset->getReference(null);
        }
        $variation = $reference->getVariation($channel);

        $this->getAssetFileUpdater()->deleteVariationFile($variation);
        $this->getVariationsaver()->save($variation, ['schedule' => true]);
    }

    /**
     * @return EnterpriseFixturesContext
     */
    protected function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }

    /**
     * @return FilesUpdaterInterface
     */
    protected function getAssetFileUpdater()
    {
        /** @var Container $container */
        $container = $this->getMainContext()->getContainer();
        return $container->get('pimee_product_asset.updater.files');
    }

    /**
     * @return AssetVariationSaver
     */
    protected function getVariationsaver()
    {
        /** @var Container $container */
        $container = $this->getMainContext()->getContainer();
        return $container->get('pimee_product_asset.saver.variation');
    }
}
