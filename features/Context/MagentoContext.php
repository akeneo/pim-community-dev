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
use Akeneo\Component\MagentoAdminExtractor\Manager\MagentoAdminConnectionManager;
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
     * @param string    $type
     * @param TableNode $table
     *
     * @Then /^I check if "([^"]*)" were sent in Magento:$/
     */
    public function iCheckIfWereSentInMagento($type, TableNode $table)
    {
        $adminUrl   = 'http://magento.local/index.php/admin';
        $adminLogin = 'root';
        $adminPwd   = 'akeneo2014';

        $connectionManager  = new MagentoAdminConnectionManager($adminUrl, $adminLogin, $adminPwd);

        try {
            $mainPageCrawler = $connectionManager->connectToAdminPage();
            $client          = $connectionManager->getClient();
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
                $productsToCheck      = $this->productsTransformer($table->getHash());
                $magExtractedProducts = $this
                    ->extractProductsFromMagentoAdmin($navigationManager, $mainPageCrawler);
                $this->checkProducts($magExtractedProducts, $productsToCheck);
                break;

            case 'categories':
            case 'category':
                $categoriesToCheck      = $this->categoriesTransformer($table->getHash());
                $magExtractedCategories = $this
                    ->extractCategoriesFromMagentoAdmin($navigationManager, $mainPageCrawler);
                $magentoCategoryTree = $this->extractedMagentoCategoriesTransformer($magExtractedCategories);
                $this->checkCategories($categoriesToCheck, $magentoCategoryTree);
                break;
        }
    }

    /**
     * @param array $table
     */
    protected function productsTransformer(array $table)
    {
        $products = [];
        foreach ($table as $row) {
            $newRow = $row;
            unset($newRow['store_view'], $newRow['sku']);

            foreach ($newRow as $paramName => $param) {
                if (empty($param)) {
                    unset($newRow[$paramName]);
                }
            }

            if (empty($row['store_view'])) {
                if (isset($newRow['attribute'])) {
                    $products[$row['sku']]['notLocalized'][$newRow['attribute']] = $newRow['value'];
                } elseif (isset($newRow['type'])) {
                    $products[$row['sku']]['notLocalized']['type'] = $newRow['type'];
                } elseif (isset($newRow['associated'])) {
                    $products[$row['sku']]['notLocalized']['associated'][$newRow['associated']][] = $newRow['value'];
                }
            } else {
                $products[$row['sku']][$row['store_view']][$newRow['attribute']] = $newRow['value'];
            }
        }

        return $products;
    }

    /**
     * Transform an array of attributes
     * Returns ['attribute1' => 'value1', 'store_view' => [ 'store_view1' => ['title' => '', 'options => ['value', '']]]]
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
     * Remove " (.*)" from the name of extracted magento categories
     *
     * @param array $extractedMagentoCategories
     *
     * @return array
     */
    protected function extractedMagentoCategoriesTransformer(array $extractedMagentoCategories)
    {
        foreach ($extractedMagentoCategories as &$tree) {
            array_walk_recursive(
                $tree,
                function (&$category) {
                    if (preg_match('#(.*) \(.*\)#', $category, $matches)) {
                        $category = $matches[1];
                    }
                }
            );
        }

        return $extractedMagentoCategories;
    }

    /**
     * Remove " (.*)" and lower characters from the name of extracted magento products categories
     *
     * @param array $magProduct
     *
     * @return array $magProduct
     */
    protected function transformProductCategories(array $magProduct)
    {
        foreach ($magProduct['categories'] as &$category) {
            if (preg_match('#(.*) \(.*\)#', $category, $matches)) {
                $category = strtolower($matches[1]);
            }
        }

        return $magProduct;
    }

    /**
     * Transform and prune the given categories gherkin array
     * Returns ['store view label 1' => [['text' => 'cat label', 'parent' => 'parent label'], ...], ... ]
     *
     * @param array $table
     *
     * @return array
     */
    protected function categoriesTransformer(array $table)
    {
        $sortedCategories      = [];
        $rootCategoryStoreView = [];

        foreach ($table as $category) {
            $categoryStoreView = $category['store_view'];

            if (!empty($category['root'])) {
                $rootCategoryStoreView[$categoryStoreView] = $category['root'];
            }

            unset($category['store_view']);
            unset($category['root']);
            $sortedCategories[$categoryStoreView][] = $category;
        }

        return $sortedCategories;
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
     * Returns [[['store view label' => ['nameOfAttribute' => ['value', 'value2', ...], ...], ...], ...], ...]
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
     * Returns ['store view label 1' => ['param_1' => 'value', ..., 'children' => idem], ...]
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

                                        if (empty($translatableParam)) {
                                            break;
                                        }

                                        foreach ($matchingMagAttribute[$paramCode] as $magTranslatableParam) {
                                            if ($translatableParam === $magTranslatableParam[$storeView]) {
                                                continue 2;
                                            }
                                        }

                                        throw new \InvalidArgumentException(
                                            sprintf('Parameter "%s" in "%s" attribute in store view "%s" not found' .
                                                'in Magento.', $paramCode, $attributeCode, $storeView)
                                        );
                                    }
                                }

                            } else {
                                throw new \InvalidArgumentException(
                                    sprintf('Parameter "%s" in "%s" attribute not found in Magento',
                                        $paramCode, $attributeCode)
                                );
                            }

                        } else {
                            if ($matchingMagAttribute[$paramCode] !== $param) {
                                throw new \InvalidArgumentException(
                                    sprintf('Parameter "%s" in "%s" attribute not found in Magento',
                                        $paramCode, $attributeCode)
                                );
                            }
                        }

                    } else {
                        throw new \InvalidArgumentException(
                            sprintf('Attribute "%s" has no parameter "%s" in Magento',
                            $attributeCode, $paramCode)
                        );
                    }
                }

            } else {
                throw new \InvalidArgumentException(
                    sprintf('Attribute with code "%s" not found in Magento', $attributeCode)
                );
            }

        }
    }

    /**
     * Checks if given categories are in the given tree
     *
     * @param array $categoriesToCheck      ['store view label' => [['text' => 'cat label', 'parent' => 'parent label'],...],...]
     * @param array $magExtractedCategories Extracted Magento categories
     *
     * @throws \InvalidArgumentException If one of categories to check is not found
     *
     * @return null
     */
    protected function checkCategories(array $categoriesToCheck, array $magExtractedCategories)
    {
        foreach ($categoriesToCheck as $storeView => $categories) {
            foreach ($categories as $category) {
                if($this->isCategoryInTree($category, $magExtractedCategories[$storeView]) === false) {
                    throw new \InvalidArgumentException(
                        sprintf('Category "%s" with "%s" parent not found in store view "%s" in Magento category tree',
                            $category['text'], $category['parent'], $storeView)
                    );
                }
            }
        }
    }

    /**
     * Checks if given products are in Magento extracted products
     *
     * @param array $magExtractedProducts
     * @param array $productsToCheck
     *
     * @throws \InvalidArgumentException
     */
    protected function checkProducts(array $magExtractedProducts, array $productsToCheck)
    {
        foreach ($productsToCheck as $sku => $product) {
            $magProduct = $this->getMagentoProductBySku($magExtractedProducts, $sku);

            foreach ($product as $storeView => $attributes) {
                if ('notLocalized' === $storeView) {
                    foreach ($attributes as $attributeName => $attribute) {
                        switch ($attributeName) {
                            case 'category':
                            case 'categories':
                                if (!is_array($attribute)) {
                                    $attribute = [$attribute];
                                }

                                $magProduct = $this->transformProductCategories($magProduct);
                                $this->compareProductCategories($attribute, $magProduct, $sku);
                            break;

                            case 'type':
                                if ($attribute !== $magProduct['type']) {
                                    throw new \InvalidArgumentException(
                                        sprintf('Product type "%s" not matched with the Magento product "%s"',
                                            $attribute, $sku)
                                    );
                                }
                            break;

                            case 'associated':
                            case 'association':
                                $this->compareAssociatedProducts($attribute, $magProduct, $sku);
                            break;

                            case 'set_name':
                            case 'attribute set':
                                if ($attribute !== $magProduct['set_name']) {
                                    throw new \InvalidArgumentException(
                                        sprintf('Attribute set "%s" not matched with the Magento product "%s"',
                                            $attribute, $sku)
                                    );
                                }
                            break;

                            default:
                                throw new \InvalidArgumentException(
                                    sprintf('Attribute type "%s" can not be compared with Magento products.' .
                                        'You need to implement it.', $attributeName, $sku)
                                );
                        }
                    }
                } else {
                    if (isset($magProduct[$storeView])) {
                        foreach ($attributes as $attributeName => $attribute) {
                            $attributeFound = false;

                            if (is_numeric($attribute)) {
                                $attribute = floatval($attribute);
                            }

                            while ((list($magAttributeName, $magAttribute) = each($magProduct[$storeView])) &&
                                $attributeFound === false
                            ) {
                                if (is_numeric($magAttribute)) {
                                    $magAttribute = floatval($magAttribute);
                                }

                                if ($magAttributeName === $attributeName && $magAttribute === $attribute) {
                                    $attributeFound = true;
                                }
                            }
                            reset($magProduct[$storeView]);

                            if (false === $attributeFound) {
                                throw new \InvalidArgumentException(
                                    sprintf('No match found for attribute "%s" or its value "%s"' .
                                        ' with the Magento product "%s"', $attributeName, $attribute, $sku)
                                );
                            }
                        }
                    } else {
                        throw new \InvalidArgumentException(
                            sprintf('Store view "%s" not found in product "%s"', $storeView, $sku)
                        );
                    }
                }
            }
        }
    }

    /**
     * Compare product associations with magento product
     *
     * @param array  $associations
     * @param array  $magProduct
     * @param string $sku
     *
     * @throws \InvalidArgumentException
     */
    protected function compareAssociatedProducts(array $associations, array $magProduct, $sku)
    {
        foreach ($associations as $associationType => $associatedProducts) {
            $associationFound = false;

            while ((list($magAssocType, $magAssocProducts) = each($magProduct['associated'])) &&
                false === $associationFound
            ) {
                if ($magAssocType === $associationType) {
                    $associationFound = true;

                    foreach ($associatedProducts as $associatedProduct) {
                        $assocProductFound = false;

                        while ((list($key, $magAssocProduct) = each($magAssocProducts)) &&
                            false === $assocProductFound
                        ) {
                            if ($magAssocProduct['SKU'] === $associatedProduct) {
                                $assocProductFound = true;
                            }
                        }
                        reset($magAssocProducts);

                        if (false === $assocProductFound) {
                            throw new \InvalidArgumentException(
                                sprintf('Product association "%s" with "%s" not found in product "%s"',
                                    $associationType, $associatedProduct, $sku)
                            );
                        }
                    }
                }
            }
            reset($magProduct['associated']);

            if (false === $associationFound) {
                throw new \InvalidArgumentException(
                    sprintf('Product association "%s" not found in product "%s"', $associationType, $sku)
                );
            }
        }
    }

    /**
     * Compare product categories with magento product
     *
     * @param array  $categories
     * @param array  $magProduct
     * @param string $sku
     *
     * @throws \InvalidArgumentException
     */
    protected function compareProductCategories(array $categories, array $magProduct, $sku)
    {
        foreach ($categories as $category) {
            $categoryFound = false;

            while ((list($key, $magCategory) = each($magProduct['categories'])) &&
                false === $categoryFound
            ) {
                if ($magCategory === $category) {
                    $categoryFound = true;
                }
            }
            reset($magProduct['categories']);

            if (false === $categoryFound) {
                throw new \InvalidArgumentException(
                    sprintf('No match found for category "%s" with the Magento product "%s"',
                        $category, $sku)
                );
            }
        }
    }

    /**
     * Search the matching Magento product with the given sku
     *
     * @param $magExtractedProducts
     * @param $sku
     *
     * @return null|array
     */
    protected function getMagentoProductBySku($magExtractedProducts, $sku)
    {
        $productFound = false;
        while ((list($key, $magProduct) = each($magExtractedProducts)) && $productFound === false) {
            reset($magProduct);
            $firstStoreView = current($magProduct);
            if ($firstStoreView['sku'] === $sku) {
                $productFound = true;
                $product = $magProduct;
            }
        }
        reset($magExtractedProducts);

        return !empty($product) ? $product : null;
    }

    /**
     * Checks if a category is in the given tree with good name and parent
     * Recursive function
     *
     * @param array  $needle Category to search in tree ['text' => 'cat label', 'parent' => 'parent label']
     * @param array  $tree   Extracted Magento categories
     * @param string $parent Label of the previous child used to check the parent
     *
     * @return bool
     */
    protected function isCategoryInTree(array $needle, array $tree, $parent = null)
    {
        $needleFound = false;

        foreach ($tree as $child) {

            if ($child['text'] === $needle['text'] && $parent === $needle['parent']) {
                $needleFound = true;
                break;

            } else if (!empty($child['children'])) {
                if ($this->isCategoryInTree($needle, $child['children'], $child['text']) === true) {
                    $needleFound = true;
                    break;
                } else {
                    $needleFound = false;
                }
            }
        }

        return $needleFound;
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
