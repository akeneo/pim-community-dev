<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;
use Oro\Bundle\UserBundle\Entity\Role;
use Behat\Mink\Exception\ElementNotFoundException;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Component\MagentoAdminExtractor\Extractor\ProductAttributeExtractor;
use Akeneo\Component\MagentoAdminExtractor\Extractor\AttributeExtractor;
use Akeneo\Component\MagentoAdminExtractor\Extractor\CategoriesExtractor;
use Akeneo\Component\MagentoAdminExtractor\Manager\MagentoAdminConnexionManager;
use Akeneo\Component\MagentoAdminExtractor\Manager\NavigationManager;
use Akeneo\Component\MagentoAdminExtractor\Manager\LogInException;

/**
 * Context for Magento connector
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoContext extends RawMinkContext implements PageObjectAwareInterface
{
    /** @var PageFactory $pageFactory */
    protected $pageFactory = null;

    /**
     * @param PageFactory $pageFactory
     */
    public function setPageFactory(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    /**
     * @Given /^I fill in the "([^"]*)" mapping:$/
     */
    public function iFillTheMapping($arg1, TableNode $table)
    {
        $page = $this->getSession()->getPage();

        $mappingAreas = $page->findAll('css', 'div.mapping-field');
        $mappingElements = [];
        foreach ($mappingAreas as $mappingElement) {
            if (null !== $mappingElement->find('css', 'label[for*=' . $arg1 .']')) {
                $sourceElement = $mappingElement->find(
                    'css',
                    'div.mapping-source ul.select2-choices,
                        div.mapping-source a.select2-choice'
                );

                $targetElement = $mappingElement->find(
                    'css',
                    'div.mapping-target ul.select2-choices,
                        div.mapping-target a.select2-choice'
                );
                break;
            }
        }

        if (isset($sourceElement) && isset($targetElement)) {
            $mapping = [];
            foreach ($table->getRows() as $row) {
                $mapping[] = ['source' => $row[0], 'target' => $row[1]];
            }

            // TODO : Add a foreach($mapping) for multiple rows mapping cases
            $sourceElement->click();
            $foundSource = false;
            $sourceOptions = $page->findAll('css', 'div.select2-result-label');
            foreach ($sourceOptions as $sourceOption) {
                if (false !== strpos($sourceOption->getHtml(), $mapping[0]['source'])) {
                    $sourceOption->click();
                    $foundSource = true;
                    break;
                }
            }
            if (false === $foundSource) {
                throw new ElementNotFoundException(
                    $this->getSession(),
                    $mapping[0]['source'],
                    'css'
                );
            }

            $targetElement->click();
            $foundTarget = false;
            $targetOptions = $page->findAll('css', 'div.select2-result-label');
            foreach ($targetOptions as $targetOption) {
                if (false !== strpos($targetOption->getHtml(), $mapping[0]['target'])) {
                    $targetOption->click();
                    $foundTarget = true;
                    break;
                }
            }
            if (false === $foundTarget) {
                throw new ElementNotFoundException(
                    $this->getSession(),
                    $arg1 . ' mapping',
                    'css',
                    $mapping[0]['target']
                );
            }
        } else {
            throw new ElementNotFoundException(
                $this->getSession(),
                $arg1 . ' mapping',
                'css',
                'label[for*=' . $arg1 .']'
            );
        }
    }

    /**
     * @param string $button
     * @param int    $timeToWait in seconds
     *
     * @Given /^I press the "([^"]*)" button and I wait "([^"]*)"s$/
     */
    public function iPressTheButtonAndIWait($button, $timeToWait)
    {
        $this->getSession()->getPage()->pressButton($button);
        $this->getMainContext()->wait($timeToWait * 1000);
    }

    /**
     * @Then /^I check if "([^"]*)" were sent in Magento:$/
     */
    public function iCheckIfWereSentInMagento($type, TableNode $table)
    {
        $adminUrl   = 'http://magento.local/index.php/admin';
        $adminLogin = 'root';
        $adminPwd   = 'akeneo2014';

        $connexionManager  = new MagentoAdminConnexionManager($adminUrl, $adminLogin, $adminPwd);

        try {
            $mainPageCrawler = $connexionManager->connectToAdminPage();
            $client          = $connexionManager->getClient();
        } catch (LogInException $e) {
            die($e->getMessage() . PHP_EOL);
        }

        $navigationManager = new NavigationManager($client);

        switch ($type) {
            case 'attributes':
            case 'attribute':
                $attributesToCheck       = $this->attributesTransformer($table->getHash());
                $magExtractedAttributes  = $this
                    ->extractAttributesFromMagentoAdmin($navigationManager, $mainPageCrawler);
                $this->checkAttributes($magExtractedAttributes, $attributesToCheck);
                break;

            case 'products':
            case 'product':
                $extractedData = $this
                    ->extractProductsFromMagentoAdmin($navigationManager, $mainPageCrawler);
                break;

            case 'categories':
            case 'category':
                $extractedData = $this
                    ->extractCategoriesFromMagentoAdmin($navigationManager, $mainPageCrawler);
                break;
        }
    }

    /**
     * Transform an array of attributes
     * Return ['attribute1' => 'value1', 'store_view' => [ 'store_view1' => ['title' => '', 'options => ['value', '']]]]
     *
     * @param array $table Given attributes
     *
     * @return array
     */
    protected function attributesTransformer(array $table)
    {
        $lastKey = 0;
        $dataToCheck = [['attribute_code' => '']];
        foreach ($table as $row) {

            if ($dataToCheck[$lastKey]['attribute_code'] === $row['attribute_code']) {
                $dataToCheck[$lastKey]['options'][$row['store_view']] = explode(', ', $row['options']);
                $dataToCheck[$lastKey]['title'][$row['store_view']] = [$row['title']];
            } else {
                $newRow = $row;
                unset($newRow['store_view'], $newRow['title'], $newRow['options']);
                $newRow['options'][$row['store_view']] = explode(', ', $row['options']);
                $newRow['title'][$row['store_view']] = [$row['title']];
                $dataToCheck[++$lastKey] = $newRow;
            }
        }

        unset($dataToCheck[0]);

        return $dataToCheck;
    }

    /**
     * Allows to extract attributes
     * Returns ['param_1' => ['value1', ...], ...]
     *
     * @param NavigationManager $navigationManager Navigation manager
     * @param Crawler           $mainPageCrawler   Admin page crawler
     *
     * @return array
     */
    protected function extractAttributesFromMagentoAdmin(NavigationManager $navigationManager, $mainPageCrawler)
    {
        $attributeExtractor = new AttributeExtractor($navigationManager);
        $attributeCatalogCrawler = $navigationManager->goToAttributeCatalog($mainPageCrawler, 1000);

        return $attributeExtractor->filterRowsAndExtract($attributeCatalogCrawler);
    }

    /**
     * Allows to extract product attributes
     * Returns ['param_1' => ['value1', ...], ...]
     *
     * @param NavigationManager $navigationManager Navigation manager
     * @param Crawler           $mainPageCrawler   Admin page crawler
     *
     * @return array
     */
    protected function extractProductsFromMagentoAdmin(NavigationManager $navigationManager, $mainPageCrawler)
    {
        $productAttributeExtractor = new ProductAttributeExtractor($navigationManager);
        $productCatalogCrawler = $navigationManager->goToProductCatalog($mainPageCrawler, 1000);

        return $productAttributeExtractor->filterRowsAndExtract($productCatalogCrawler);
    }

    /**
     * Allows to extract categories
     * Returns ['param_1' => ['value1', ...], ...]
     *
     * @param NavigationManager $navigationManager Navigation manager
     * @param Crawler           $mainPageCrawler   Admin page crawler
     *
     * @return array
     */
    protected function extractCategoriesFromMagentoAdmin(NavigationManager $navigationManager, $mainPageCrawler)
    {
        $categoriesExtractor = new CategoriesExtractor($navigationManager);

        return $categoriesExtractor->extract($mainPageCrawler);
    }

    /**
     * It checks if attributes given from Behat and attributes extracted from Magento are matching
     *
     * @param $magExtractedAttributes array Attributes extracted from Magento
     * @param $attributesToCheck      array Attributes given from behat
     *
     * @throws \InvalidArgumentException
     *
     * @return null
     */
    protected function checkAttributes($magExtractedAttributes, $attributesToCheck)
    {
        print_r($attributesToCheck);
        print_r($magExtractedAttributes);
        foreach ($attributesToCheck as $attribute) {
            $attributeCode = $attribute['attribute_code'];

            $matchingMagAttribute = [];
            foreach ($magExtractedAttributes as $magAttribute) {
                if ($attributeCode === $magAttribute['attribute_code']) {
                    $matchingMagAttribute = $magAttribute;
                    break;
                }
            }

            if (!empty($matchingMagAttribute)) {
                foreach ($attribute as $paramCode => $param) {

                    if (isset($matchingMagAttribute[$paramCode])) {

                        if (is_array($param)) {
                            if (is_array($matchingMagAttribute[$paramCode])) {

                                foreach ($param as $storeView => $translatableParams) {
                                    foreach ($translatableParams as $translatableParam) {

                                        foreach ($matchingMagAttribute[$paramCode] as $magTranslatableParam) {
                                            if ($translatableParam === $magTranslatableParam[$storeView]) {
                                                continue 2;
                                            }
                                        }

                                        throw new \InvalidArgumentException("Parameter \"$paramCode\" in
                                        \"$attributeCode\" in store view \"$storeView\" not matching in Magento");
                                    }
                                }

                            } else {
                                throw new \InvalidArgumentException("Parameter \"$paramCode\" in \"$attributeCode\"
                                    not matching in Magento");
                            }

                        } else {
                            if ($matchingMagAttribute[$paramCode] !== $param) {
                                throw new \InvalidArgumentException("Parameter \"$paramCode\" in \"$attributeCode\"
                                    not matching in Magento");
                            }
                        }

                    } else {
                        throw new \InvalidArgumentException("Attribute \"$attributeCode\" has no parameter
                            \"$paramCode\" in Magento");
                    }
                }

            } else {
                throw new \InvalidArgumentException("Attribute with code \"$attributeCode\" not found in Magento");
            }

        }
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getMainContext()->getContainer();
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Manager\ProductManager
     */
    protected function getProductManager()
    {
        return $this->getContainer()->get('pim_catalog.manager.product');
    }

    /**
     * @BeforeScenario
     */
    public function purgeMagentoDatabase()
    {
        exec('mysql -u root -proot magento < '. __DIR__ . '/fixtures/dump_magento_1_8.sql');
    }
}
