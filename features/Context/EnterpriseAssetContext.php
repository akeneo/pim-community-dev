<?php

namespace Context;

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\MinkExtension\Context\RawMinkContext;
use Context\Page\Asset\Edit;
use Context\Spin\SpinCapableTrait;
use PimEnterprise\Bundle\ProductAssetBundle\Doctrine\Common\Saver\AssetVariationSaver;
use PimEnterprise\Component\ProductAsset\Updater\FilesUpdaterInterface;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Overrided context
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseAssetContext extends RawMinkContext
{
    use SpinCapableTrait;

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
    public function iCanGenerateChannel($not, $channel)
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
     * @Given /^I upload the reference file ([^"]+)$/
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
                return (bool) $iconContainer->find('css', 'i.validation-tooltip');
            }, 'Cannot find validation tooltip');
        }
    }

    /**
     * @param array $assets
     *
     * @Then /^I should not see assets? (.*)$/
     */
    public function iShouldNotSeeAssets($assets)
    {
        $this->getMainContext()->getSubcontext('datagrid')->iShouldNotSeeEntities($assets);
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
            $file = rtrim(
                realpath($this->getMinkParameter('files_path')),
                DIRECTORY_SEPARATOR
            ) . DIRECTORY_SEPARATOR . $file;
        }

        if (null === $channel) {
            $uploadZone = $this->getCurrentPage()->findReferenceUploadZone();
        } else {
            $uploadZone = $this->getCurrentPage()->findVariationUploadZone($channel);
        }

        $field = $uploadZone->find('css', 'input[type="file"]');
        $field->attachFile($file);
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
        $this->getVariationSaver()->save($variation, ['schedule' => true]);
    }

    /**
     * @param TableNode $table
     *
     * @Given /^I select the assets to upload:$/
     */
    public function iSelectAssetsToUpload(TableNode $table)
    {
        $fullPath = '';

        if ($this->getMinkParameter('files_path')) {
            $fullPath = rtrim(realpath($this->getMinkParameter('files_path')), DIRECTORY_SEPARATOR)
                . DIRECTORY_SEPARATOR;
        }

        foreach ($table->getHash() as $data) {
            $this->getMainContext()->executeScript(
                "document.querySelector('.dz-hidden-input').style.visibility = 'visible';
                document.querySelector('.dz-hidden-input').style.height = '10px';
                document.querySelector('.dz-hidden-input').style.width = '10px';
                document.querySelector('.dz-hidden-input').style.display = 'block';"
            );
            $uploadContainer = $this->getCurrentPage()->find('css', '.dz-hidden-input');

            $file = $fullPath . $data['name'];
            if (is_file($file)) {
                $uploadContainer->attachFile($file);
            }
        }
    }

    /**
     * @Then /^I should see "([^"]+)" status for asset "([^"]+)"$/
     *
     * @param string $text
     * @param string $asset
     *
     * @throws ElementNotFoundException
     */
    public function iShouldSeeStatusForAssetUpload($text, $asset)
    {
        $this->spin(function () use ($text, $asset) {
            $assetElements = $this->getCurrentPage()
                ->findAll('css', sprintf('td:contains("%s")', $asset));

            $found = null;

            if (!is_array($assetElements)) {
                $assetElements = [$assetElements];
            }

            foreach ($assetElements as $assetElement) {
                $row   = $assetElement->getParent();
                $found = $row->find('css', sprintf('td.status:contains("%s")', $text));
                if ($found) {
                    break;
                }
            }

            return $found;
        }, sprintf('Unable to find %s for asset %s', $text, $asset));
    }

    /**
     * @When /^I (start|schedule|cancel) assets mass upload$/
     */
    public function iDoAssetMassUploadAction($action)
    {
        $actionButton = null;
        $currentPage  = $this->getCurrentPage();

        if ('start' === $action) {
            $actionButton = $this->spin(function () use ($currentPage) {
                return $currentPage->find('css', '.btn.start');
            }, sprintf('Unable to find the %s buton for mass upload', $action));
        }
        if ('schedule' === $action) {
            $actionButton = $this->spin(function () use ($currentPage) {
                return $currentPage->find('css', '.btn.schedule');
            }, sprintf('Unable to find the %s buton for mass upload', $action));
        }
        if ('cancel' === $action) {
            $actionButton = $this->spin(function () use ($currentPage) {
                return $currentPage->find('css', '.btn.cancel');
            }, sprintf('Unable to find the %s buton for mass upload', $action));
        }
        $actionButton->click();
        $this->getMainContext()->wait();
    }

    /**
     * @When /^I (delete) asset upload$/
     */
    public function iDoAssetUploadAction($action)
    {
        $actionButton = null;
        $currentPage  = $this->getCurrentPage();

        if ('delete' === $action) {
            $actionButton = $this->spin(function () use ($currentPage) {
                return $currentPage->find('css', '.btn.delete');
            }, sprintf('Unable to find the %s buton for upload', $action));
        }
        $actionButton->click();
        $this->getMainContext()->wait();
    }

    /**
     * @param string $assetCode
     * @param string $categoryCodes
     *
     * @Given /^asset categor(?:y|ies) of "([^"]*)" should be "([^"]*)"$/
     */
    public function theAssetCategoriesOfShouldBe($assetCode, $categoryCodes)
    {
        $asset = $this->getFixturesContext()->getAsset($assetCode);

        $categories = $asset->getCategories()->map(
            function ($category) {
                return $category->getCode();
            }
        )->toArray();
        assertEquals($this->getMainContext()->listToArray($categoryCodes), $categories);
    }

    /**
     * @Given /^the asset temporary file storage has been cleared$/
     */
    public function clearAssetTmpFileStorage()
    {
        $fileSystem = $this->getMainContext()->getContainer()->get('oneup_flysystem.tmp_storage_filesystem');
        foreach ($fileSystem->listFiles('', true) as $file) {
            $fileSystem->delete($file['path']);
        }
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
        $container = $this->getMainContext()->getContainer();

        return $container->get('pimee_product_asset.updater.files');
    }

    /**
     * @return AssetVariationSaver
     */
    protected function getVariationSaver()
    {
        $container = $this->getMainContext()->getContainer();

        return $container->get('pimee_product_asset.saver.variation');
    }
}
