<?php

namespace Context;

use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Util\Inflector;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Gherkin\Node\PyStringNode;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\CatalogBundle\Model\Metric;

/**
 * A context for creating entities
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FixturesContext extends RawMinkContext
{
    protected $locales = array(
        'english' => 'en_US',
        'french'  => 'fr_FR',
        'german'  => 'de_DE',
    );

    protected $attributeTypes = array(
        'text'         => 'pim_catalog_text',
        'number'       => 'pim_catalog_number',
        'textarea'     => 'pim_catalog_textarea',
        'identifier'   => 'pim_catalog_identifier',
        'metric'       => 'pim_catalog_metric',
        'prices'       => 'pim_catalog_price_collection',
        'image'        => 'pim_catalog_image',
        'file'         => 'pim_catalog_file',
        'multiselect'  => 'pim_catalog_multiselect',
        'simpleselect' => 'pim_catalog_simpleselect',
        'date'         => 'pim_catalog_date',
    );

    protected $entities = array(
        'Attribute'       => 'PimCatalogBundle:Attribute',
        'AttributeGroup'  => 'PimCatalogBundle:AttributeGroup',
        'AttributeOption' => 'PimCatalogBundle:AttributeOption',
        'Channel'         => 'PimCatalogBundle:Channel',
        'Currency'        => 'PimCatalogBundle:Currency',
        'Family'          => 'PimCatalogBundle:Family',
        'Category'        => 'PimCatalogBundle:Category',
        'AssociationType' => 'PimCatalogBundle:AssociationType',
        'JobInstance'     => 'AkeneoBatchBundle:JobInstance',
        'User'            => 'OroUserBundle:User',
        'Role'            => 'OroUserBundle:Role',
        'Locale'          => 'PimCatalogBundle:Locale',
        'GroupType'       => 'PimCatalogBundle:GroupType',
        'Product'         => 'Pim\Bundle\CatalogBundle\Model\Product',
        'ProductGroup'    => 'Pim\Bundle\CatalogBundle\Entity\Group',
    );

    protected $placeholderValues = array();

    protected $username;

    /**
     * @BeforeScenario
     */
    public function resetPlaceholderValues()
    {
        $this->placeholderValues = array(
            '%tmp%'      => getenv('BEHAT_TMPDIR') ?: '/tmp/pim-behat',
            '%fixtures%' => __DIR__ . '/fixtures'
        );
    }

    /**
     * @BeforeScenario
     */
    public function removeTmpDir()
    {
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->remove($this->placeholderValues['%tmp%']);
    }

    /**
     * @BeforeScenario
     */
    public function clearUOW()
    {
        foreach ($this->getSmartRegistry()->getManagers() as $manager) {
            $manager->clear();
        }
    }

    /**
     * @BeforeScenario
     */
    public function clearPimFilesystem()
    {
        // FIXME: Remove gitkeep?
        $fs = $this->getPimFilesystem();
        foreach ($fs->keys() as $key) {
            if (strpos($key, '.') !== 0) {
                $fs->delete($key);
            }
        }
    }

    /**
     * Magic methods for getting and creating entities
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $args)
    {
        if ('getOrCreate' === $getter = substr($method, 0, 11)) {
            $entityName = substr($method, 11);
        } elseif ('create' === $getter = substr($method, 0, 6)) {
            $entityName = substr($method, 6);
        } elseif ('find' === $getter = substr($method, 0, 4)) {
            $entityName = substr($method, 4);
        } elseif ('get' === $getter = substr($method, 0, 3)) {
            $entityName = substr($method, 3);
        } else {
            $getter = null;
            $entityName = null;
        }

        if ($getter && array_key_exists($entityName, $this->entities)) {
            $method = $getter . 'Entity';

            return $this->$method($entityName, $args[0]);
        }

        throw new \BadMethodCallException(sprintf('There is no method named %s in FixturesContext', $method));
    }

    /**
     * @param string $entityName
     * @param mixed  $data
     *
     * @throws InvalidArgumentException If entity is not found
     *
     * @return object
     */
    public function getEntity($entityName, $data)
    {
        $getter = sprintf('get%s', $entityName);

        if (method_exists($this, $getter)) {
            return $this->$getter($data);
        }

        return $this->getEntityOrException($entityName, $data);
    }

    /**
     * @param string $entityName
     * @param mixed  $data
     *
     * @return object
     */
    public function createEntity($entityName, $data)
    {
        $method = sprintf('create%s', $entityName);

        return $this->$method($data);
    }

    /**
     * @param string $entityName
     * @param string $data
     *
     * @return object
     */
    public function getOrCreateEntity($entityName, $data)
    {
        try {
            return $this->getEntity($entityName, $data);
        } catch (\InvalidArgumentException $e) {
            return $this->createEntity($entityName, $data);
        }
    }

    /**
     * @param string $entityName
     * @param mixed  $criteria
     *
     * @return object|null
     */
    public function findEntity($entityName, $criteria)
    {
        if (!array_key_exists($entityName, $this->entities)) {
            throw new \Exception(sprintf('Unrecognized entity "%s".', $entityName));
        }

        if (gettype($criteria) === 'string' || $criteria === null) {
            $criteria = array('code' => $criteria);
        }

        return $this->getRepository($this->entities[$entityName])->findOneBy($criteria);
    }

    /**
     * @param string $entityName
     * @param mixed  $criteria
     *
     * @throws InvalidArgumentException If entity is not found
     *
     * @return object
     */
    public function getEntityOrException($entityName, $criteria)
    {
        $entity = $this->findEntity($entityName, $criteria);

        if (!$entity) {
            if (is_string($criteria)) {
                $criteria = array('code' => $criteria);
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Could not find "%s" with criteria %s',
                    $this->entities[$entityName],
                    print_r(\Doctrine\Common\Util\Debug::export($criteria, 2), true)
                )
            );
        }

        return $entity;
    }

    /**
     * @param array|string $data
     *
     * @return Product
     *
     * @Given /^a "([^"]*)" product$/
     */
    public function createProduct($data)
    {
        if (is_string($data)) {
            $data = array('sku' => $data);
        } elseif (isset($data['enabled']) && in_array($data['enabled'], array('yes', 'no'))) {
            $data['enabled'] = ($data['enabled'] === 'yes');
        }

        // Clear product transformer cache
        $this
            ->getContainer()
            ->get('pim_transform.transformer.product')
            ->reset();

        // Reset product import validator
        $this
            ->getContainer()
            ->get('pim_base_connector.validator.product_import')
            ->reset();

        $product = $this->loadFixture('products', $data);

        $this->getProductBuilder()->addMissingProductValues($product);
        $this->getProductManager()->handleMedia($product);
        $this->getProductManager()->save($product, false);

        return $product;
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following products?:$/
     */
    public function theFollowingProduct(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->createProduct($data);
        }
    }

    /**
     * @param string $status
     * @param string $sku
     *
     * @return Product
     *
     * @Given /^(?:an|a) (enabled|disabled) "([^"]*)" product$/
     */
    public function anEnabledOrDisabledProduct($status, $sku)
    {
        return $this->createProduct(
            array(
                'sku'     => $sku,
                'enabled' => $status === 'enabled' ? 'yes' : 'no'
            )
        );
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following famil(?:y|ies):$/
     */
    public function theFollowingFamilies(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->createFamily($data);
        }
    }

    /**
     * @param string $entityName
     *
     * @Given /^there is no (.*)$/
     */
    public function thereIsNoEntity($entityName)
    {
        if (strpos($entityName, ' ')) {
            $entityName = implode('', array_map('ucfirst', explode(' ', $entityName)));
        }

        $entityName = ucfirst($entityName);

        if (!array_key_exists($entityName, $this->entities)) {
            throw new \Exception(sprintf('Unrecognized entity "%s".', $entityName));
        }

        $namespace = $this->entities[$entityName];
        $entities = $this->getRepository($namespace)->findAll();

        foreach ($entities as $entity) {
            $this->remove($entity, false);
        }
        $this->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following attribute groups?:$/
     */
    public function theFollowingAttributeGroups(TableNode $table)
    {
        foreach ($table->getHash() as $index => $data) {
            $this->createAttributeGroup($data);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following attributes?:$/
     */
    public function theFollowingAttributes(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->createAttribute($data);
        }

        $this->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following attribute label translations:$/
     */
    public function theFollowingAttributeLabelTranslations(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this
                ->getAttribute($data['attribute'])
                ->setLocale($this->getLocaleCode($data['locale']))
                ->setLabel($data['label']);
        }

        $this->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following product values?:$/
     */
    public function theFollowingProductValues(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $row = array_merge(['locale' => null, 'scope' => null, 'value' => null], $row);

            $attributeCode = $row['attribute'];
            if ($row['locale']) {
                $attributeCode .= '-' . $row['locale'];
            }
            if ($row['scope']) {
                $attributeCode .= '-' . $row['scope'];
            }

            $data = [
                'sku'          => $row['product'],
                $attributeCode => $this->replacePlaceholders($row['value'])
            ];

            $this->createProduct($data);
        }

        $this->flush();
    }

    /**
     * @param string $sku
     * @param string $attributeCodes
     *
     * @Given /^the "([^"]*)" product has the "([^"]*)" attributes?$/
     */
    public function theProductHasTheAttributes($sku, $attributeCodes)
    {
        $product = $this->getProduct($sku);

        foreach ($this->listToArray($attributeCodes) as $code) {
            $this->getProductBuilder()->addAttributeToProduct($product, $this->getAttribute($code));
        }

        $this->getProductManager()->save($product);

        $this->flush();
    }

    /**
     * @param string $attribute
     * @param string $family
     *
     * @Given /^the attribute "([^"]*)" has been chosen as the family "([^"]*)" label$/
     */
    public function theAttributeHasBeenChosenAsTheFamilyLabel($attribute, $family)
    {
        $code      = $this->camelize($attribute);
        $attribute = $this->getAttribute($code);
        $family    = $this->getFamily($family);

        $family->setAttributeAsLabel($attribute);

        $this->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following categor(?:y|ies):$/
     */
    public function theFollowingCategories(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->createCategory(array($data));
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following attributes:$/
     */
    public function thereShouldBeTheFollowingAttributes(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $attribute = $this->getAttribute($data['code']);
            $this->refresh($attribute);

            assertEquals($data['label-en_US'], $attribute->getTranslation('en_US')->getLabel());
            assertEquals($this->getAttributeType($data['type']), $attribute->getAttributeType());
            assertEquals(($data['localizable'] == 1), $attribute->isLocalizable());
            assertEquals(($data['scopable'] == 1), $attribute->isScopable());
            assertEquals($data['group'], $attribute->getGroup()->getCode());
            assertEquals(($data['useable_as_grid_column'] == 1), $attribute->isUseableAsGridColumn());
            assertEquals(($data['useable_as_grid_filter'] == 1), $attribute->isUseableAsGridFilter());
            assertEquals(($data['unique'] == 1), $attribute->isUnique());
            if ($data['allowed_extensions'] != '') {
                assertEquals(explode(',', $data['allowed_extensions']), $attribute->getAllowedExtensions());
            }
            assertEquals($data['metric_family'], $attribute->getMetricFamily());
            assertEquals($data['default_metric_unit'], $attribute->getDefaultMetricUnit());
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following options:$/
     */
    public function thereShouldBeTheFollowingOptions(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $attribute = $this->getEntityOrException('Attribute', array('code' => $data['attribute']));
            $option = $this->getEntityOrException(
                'AttributeOption',
                array('code' => $data['code'], 'attribute' => $attribute)
            );
            $this->refresh($option);

            $option->setLocale('en_US');
            assertEquals($data['label-en_US'], (string) $option);
            assertEquals(($data['default'] == 1), $option->isDefault());
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following categories:$/
     */
    public function thereShouldBeTheFollowingCategories(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $category = $this->getCategory($data['code']);
            $this->refresh($category);

            assertEquals($data['label'], $category->getTranslation('en_US')->getLabel());
            if (empty($data['parent'])) {
                assertNull($category->getParent());
            } else {
                assertEquals($data['parent'], $category->getParent()->getCode());
            }
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following association types:$/
     */
    public function thereShouldBeTheFollowingAssociationTypes(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $associationType = $this->getAssociationType($data['code']);
            $this->refresh($associationType);

            assertEquals($data['label-en_US'], $associationType->getTranslation('en_US')->getLabel());
            assertEquals($data['label-fr_FR'], $associationType->getTranslation('fr_FR')->getLabel());
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following groups:$/
     */
    public function thereShouldBeTheFollowingGroups(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $group = $this->getProductGroup($data['code']);
            $this->refresh($group);

            assertEquals($data['label-en_US'], $group->getTranslation('en_US')->getLabel());
            assertEquals($data['label-fr_FR'], $group->getTranslation('fr_FR')->getLabel());
            assertEquals($data['type'], $group->getType()->getCode());

            if ($group->getType()->isVariant()) {
                $attributes = array();
                foreach ($group->getAttributes() as $attribute) {
                    $attributes[] = $attribute->getCode();
                }
                asort($attributes);
                $attributes = implode(',', $attributes);
                assertEquals($data['attributes'], $attributes);
            }
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following channels?:$/
     */
    public function theFollowingChannels(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->createChannel($data);
        }
    }

    /**
     * @param string $locale
     * @param string $channel
     *
     * @Given /^I add the "([^"]*)" locale to the "([^"]*)" channel$/
     */
    public function iAddTheLocaleToTheChannel($locale, $channel)
    {
        $channel = $this->getChannel($channel);

        $localeCode = isset($this->locales[$locale]) ? $this->locales[$locale] : $locale;
        $channel->addLocale($this->getLocale($localeCode));
        $this->persist($channel);
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following jobs?:$/
     */
    public function theFollowingJobs(TableNode $table)
    {
        $registry = $this->getContainer()->get('akeneo_batch.connectors');

        foreach ($table->getHash() as $data) {
            $jobInstance = new JobInstance($data['connector'], $data['type'], $data['alias']);
            $jobInstance->setCode($data['code']);
            $jobInstance->setLabel($data['label']);

            $job = $registry->getJob($jobInstance);
            $jobInstance->setJob($job);

            $this->persist($jobInstance);
        }
    }

    /**
     * @param string    $code
     * @param TableNode $table
     *
     * @Given /^the following job "([^"]*)" configuration:$/
     */
    public function theFollowingJobConfiguration($code, TableNode $table)
    {
        $jobInstance = $this->getJobInstance($code);
        $configuration = $jobInstance->getRawConfiguration();

        foreach ($table->getRowsHash() as $property => $value) {
            $value = $this->replacePlaceholders($value);
            if (in_array($value, array('yes', 'no'))) {
                $value = 'yes' === $value;
            }

            $configuration[$property] = $value;
        }

        $jobInstance->setRawConfiguration($configuration);
        $this->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following product groups?:$/
     */
    public function theFollowingProductGroups(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $code = $data['code'];
            $label = $data['label'];
            $type = $data['type'];

            $attributes = (!isset($data['attributes']) || $data['attributes'] == '')
                ? array() : explode(', ', $data['attributes']);

            $products = (isset($data['products'])) ? explode(', ', $data['products']) : array();

            $this->createProductGroup($code, $label, $type, $attributes, $products);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following association types?:$/
     */
    public function theFollowingAssociationTypes(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $code = $data['code'];
            $label = isset($data['label']) ? $data['label'] : null;

            $this->createAssociationType($code, $label);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following group types?:$/
     */
    public function theFollowingGroupTypes(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $code = $data['code'];
            $label = isset($data['label']) ? $data['label'] : null;
            $isVariant = isset($data['variant']) ? $data['variant'] : 0;

            $this->createGroupType($code, $label, $isVariant);
        }
    }

    /**
     * @param string $attribute
     * @param string $options
     *
     * @Given /^the following "([^"]*)" attribute options: (.*)$/
     */
    public function theFollowingAttributeOptions($attribute, $options)
    {
        $attribute = $this->getAttribute(strtolower($attribute));
        foreach ($this->listToArray($options) as $option) {
            $attribute->addOption($this->createOption($option));
        }

        $this->flush();
    }

    /**
     * @param string $lang
     * @param string $attribute
     * @param string $identifier
     * @param string $value
     *
     * @Given /^the (\w+) (\w+) of "([^"]*)" should be "([^"]*)"$/
     */
    public function theOfShouldBe($lang, $attribute, $identifier, $value)
    {
        $this->clearUOW();
        $productValue = $this->getProductValue($identifier, strtolower($attribute), $this->locales[$lang]);

        assertEquals($value, $productValue->getData());
    }

    /**
     * @param string $lang
     * @param string $scope
     * @param string $attribute
     * @param string $identifier
     * @param string $value
     *
     * @Given /^the (\w+) (\w+) (\w+) of "([^"]*)" should be "([^"]*)"$/
     */
    public function theScopableOfShouldBe($lang, $scope, $attribute, $identifier, $value)
    {
        $this->clearUOW();
        $productValue = $this->getProductValue($identifier, strtolower($attribute), $this->locales[$lang], $scope);

        assertEquals($value, $productValue->getData());
    }

    /**
     * @param string    $attribute
     * @param string    $products
     * @param TableNode $table
     *
     * @Given /^the prices "([^"]*)" of products (.*) should be:$/
     */
    public function thePricesOfProductsShouldBe($attribute, $products, TableNode $table)
    {
        $this->clearUOW();
        foreach ($this->listToArray($products) as $identifier) {
            $productValue = $this->getProductValue($identifier, strtolower($attribute));

            foreach ($table->getHash() as $price) {
                $productPrice = $productValue->getPrice($price['currency']);
                assertEquals($price['amount'], $productPrice->getData());
            }
        }
    }

    /**
     * @param string    $attribute
     * @param string    $products
     * @param TableNode $table
     *
     * @return null
     *
     * @Given /^the options "([^"]*)" of products (.*) should be:$/
     */
    public function theOptionsOfProductsShouldBe($attribute, $products, TableNode $table)
    {
        $this->clearUOW();
        foreach ($this->listToArray($products) as $identifier) {
            $productValue = $this->getProductValue($identifier, strtolower($attribute));
            $options = $productValue->getOptions();
            $optionCodes = $options->map(
                function ($option) {
                    return $option->getCode();
                }
            );

            assertEquals(count($table->getHash()), $options->count());
            foreach ($table->getHash() as $data) {
                assertContains($data['value'], $optionCodes);
            }
        }
    }

    /**
     * @param string $attribute
     * @param string $products
     * @param string $filename
     *
     * @Given /^the file "([^"]*)" of products (.*) should be "([^"]*)"$/
     */
    public function theFileOfShouldBe($attribute, $products, $filename)
    {
        $this->clearUOW();
        foreach ($this->listToArray($products) as $identifier) {
            $productValue = $this->getProductValue($identifier, strtolower($attribute));
            $media = $productValue->getMedia();
            assertEquals($filename, $media->getOriginalFilename());
        }
    }

    /**
     * @param string $attribute
     * @param string $products
     * @param string $data
     *
     * @Given /^the metric "([^"]*)" of products (.*) should be "([^"]*)"$/
     */
    public function theMetricOfProductsShouldBe($attribute, $products, $data)
    {
        $this->clearUOW();
        foreach ($this->listToArray($products) as $identifier) {
            $productValue = $this->getProductValue($identifier, strtolower($attribute));
            assertEquals($data, $productValue->getMetric()->getData());
        }
    }

    /**
     * @param PyStringNode $string
     *
     * @Given /^the following file to import:$/
     */
    public function theFollowingFileToImport(PyStringNode $string)
    {
        $this->placeholderValues['%file to import%'] = $filename =
            sprintf(
                '%s/pim-import/behat-import-%s.csv',
                $this->placeholderValues['%tmp%'],
                substr(md5(rand()), 0, 7)
            );
        @rmdir(dirname($filename));
        @mkdir(dirname($filename), 0777, true);

        file_put_contents($filename, (string) $string);
    }

    /**
     * @param TableNode $table
     *
     * @return null
     *
     * @Given /^the following CSV to import:$/
     */
    public function theFollowingCSVToImport(TableNode $table)
    {
        $delimiter = ';';

        $data = $table->getRowsHash();
        $columns = join($delimiter, array_keys($data));

        $rows = array();
        foreach ($data as $values) {
            foreach ($values as $index => $value) {
                $value = in_array($value, array('yes', 'no')) ? (int) $value === 'yes' : $value;
                $rows[$index][] = $value;
            }
        }
        $rows = array_map(
            function ($row) use ($delimiter) {
                return join($delimiter, $row);
            },
            $rows
        );

        array_unshift($rows, $columns);

        return $this->theFollowingFileToImport(new PyStringNode(join("\n", $rows)));
    }

    /**
     * @param string    $code
     * @param TableNode $table
     *
     * @Given /^import directory of "([^"]*)" contains the following media:$/
     */
    public function importDirectoryOfContainsTheFollowingMedia($code, TableNode $table)
    {
        $configuration = $this
            ->getJobInstance($code)
            ->getRawConfiguration();

        $path = dirname($configuration['filePath']);

        foreach ($table->getRows() as $data) {
            copy(__DIR__ . '/fixtures/'. $data[0], rtrim($path, '/') . '/' .$data[0]);
        }
    }

    /**
     * @param integer $expectedTotal
     *
     * @Then /^there should be (\d+) products?$/
     */
    public function thereShouldBeProducts($expectedTotal)
    {
        $total = count($this->getProductManager()->getProductRepository()->findAll());

        assertEquals($expectedTotal, $total);
    }

    /**
     * @param string    $identifier
     * @param TableNode $table
     *
     * @Given /^the product "([^"]*)" should have the following values?:$/
     */
    public function theProductShouldHaveTheFollowingValues($identifier, TableNode $table)
    {
        $this->clearUOW();
        $product = $this->getProduct($identifier);

        foreach ($table->getRowsHash() as $code => $value) {
            $productValue = $product->getValue($code);
            if ('media' === $this->getAttribute($code)->getBackendType()) {
                // media filename is auto generated during media handling and cannot be guessed
                // (it contains a timestamp)
                if ('**empty**' === $value) {
                    assertEmpty((string) $productValue);
                } else {
                    assertTrue(false !== strpos((string) $productValue, $value));
                }
            } else {
                assertEquals($value, (string) $productValue);
            }
        }
    }

    /**
     * @param string $productCode
     * @param string $familyCode
     *
     * @Given /^(?:the )?family of "([^"]*)" should be "([^"]*)"$/
     */
    public function theFamilyOfShouldBe($productCode, $familyCode)
    {
        $family = $this->getProduct($productCode)->getFamily();
        if (!$family) {
            throw \Exception(sprintf('Product "%s" doesn\'t have a family', $productCode));
        }
        assertEquals($familyCode, $family->getCode());
    }

    /**
     * @param string $productCode
     * @param string $categoryCodes
     *
     * @return null
     *
     * @Given /^(?:the )?categor(?:y|ies) of "([^"]*)" should be "([^"]*)"$/
     */
    public function theCategoriesOfShouldBe($productCode, $categoryCodes)
    {
        $product = $this->getProduct($productCode);
        $categories = $product->getCategories()->map(
            function ($category) {
                return $category->getCode();
            }
        )->toArray();
        assertEquals($this->listToArray($categoryCodes), $categories);
    }

    /**
     * @param Channel   $channel
     * @param TableNode $conversionUnits
     *
     * @Given /^the following (channel "(?:[^"]*)") conversion options:$/
     */
    public function theFollowingChannelConversionOptions(Channel $channel, TableNode $conversionUnits)
    {
        $channel->setConversionUnits($conversionUnits->getRowsHash());

        $this->flush();
    }

    /**
     * @param string $group
     * @param array  $products
     *
     * @Then /^"([^"]*)" group should contain "([^"]*)"$/
     */
    public function groupShouldContain($group, $products)
    {
        $group = $this->getProductGroup($group);
        $this->refresh($group);
        $groupProducts = $group->getProducts();

        foreach ($this->listToArray($products) as $sku) {
            if (!$groupProducts->contains($this->getProduct($sku))) {
                throw new \Exception(
                    sprintf('Group "%s" doesn\'t contain product "%s"', $group->getCode(), $sku)
                );
            }
        }
    }

    /**
     * @param string $roleLabel
     *
     * @return \Oro\Bundle\UserBundle\Entity\Role
     */
    public function getRole($roleLabel)
    {
        return $this->getEntityOrException('Role', array('label' => $roleLabel));
    }

    /**
     * @param string $sku
     *
     * @return Product
     */
    public function getProduct($sku)
    {
        $product = $this->getProductManager()->findByIdentifier($sku);

        if (!$product) {
            throw new \InvalidArgumentException(sprintf('Could not find a product with sku "%s"', $sku));
        }

        $this->refresh($product);

        return $product;
    }

    /**
     * @param string $username
     *
     * @return User
     *
     * @Then /^there should be a "([^"]*)" user$/
     */
    public function getUser($username)
    {
        return $this->getEntityOrException('User', array('username' => $username));
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @param string $attribute
     *
     * @Given /^I\'ve removed the "([^"]*)" attribute$/
     */
    public function iVeRemovedTheAttribute($attribute)
    {
        $this->remove($this->getAttribute($attribute));
    }

    /**
     * @param string $product
     * @param string $family
     *
     * @Given /^I set product "([^"]*)" family to "([^"]*)"$/
     */
    public function iSetProductFamilyTo($product, $family)
    {
        $this
            ->getProduct($product)
            ->setFamily($this->getFamily($family));

        $this->flush();
    }

    /**
     * @Then /^attribute "([^"]*)" should be required in family "([^"]*)" for channel "([^"]*)"$/
     */
    public function attributeShouldBeRequiredInFamilyForChannel($attribute, $family, $channel)
    {
        $requirement = $this->getAttributeRequirement($attribute, $family, $channel);

        assertNotNull($requirement);
        assertTrue($requirement->isRequired());
    }

    /**
     * @Given /^attribute "([^"]*)" should be optional in family "([^"]*)" for channel "([^"]*)"$/
     */
    public function attributeShouldBeOptionalInFamilyForChannel($attribute, $family, $channel)
    {
        $requirement = $this->getAttributeRequirement($attribute, $family, $channel);

        assertNotNull($requirement);
        assertFalse($requirement->isRequired());
    }


    /**
     * @param string $attributeCode
     * @param string $familyCode
     * @param string $channelCode
     */
    protected function getAttributeRequirement($attributeCode, $familyCode, $channelCode)
    {
        $em = $this->getEntityManager();
        $repo = $em->getRepository('PimCatalogBundle:AttributeRequirement');

        $attribute = $this->getAttribute($attributeCode);
        $family = $this->getFamily($familyCode);
        $channel = $this->getChannel($channelCode);

        return $repo->findOneBy(
            [
                'attribute' => $attribute,
                'family' => $family,
                'channel' => $channel,
            ]
        );
    }

    /**
     * @param string $language
     *
     * @return string
     */
    public function getLocaleCode($language)
    {
        if ('default' === $language) {
            return $language;
        }

        if (!isset($this->locales[$language])) {
            throw new \InvalidArgumentException(sprintf('Undefined language "%s"', $language));
        }

        return $this->locales[$language];
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function replacePlaceholders($value)
    {
        return strtr($value, $this->placeholderValues);
    }

    /**
     * @param string $identifier
     * @param string $attribute
     * @param string $locale
     * @param string $scope
     *
     * @throws InvalidArgumentException
     *
     * @return ProductValue
     */
    protected function getProductValue($identifier, $attribute, $locale = null, $scope = null)
    {
        if (null === $product = $this->getProduct($identifier)) {
            throw new \InvalidArgumentException(sprintf('Could not find product with identifier "%s"', $identifier));
        }

        if (null === $value = $product->getValue($attribute, $locale, $scope)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not find product value for attribute "%s" in locale "%s" for scope "%s"',
                    $attribute,
                    $locale,
                    $scope
                )
            );
        }

        return $value;
    }

    /**
     * @param string  $code
     * @param string  $label
     * @param boolean $isVariant
     *
     * @return GroupType
     */
    protected function createGroupType($code, $label, $isVariant)
    {
        $type = new GroupType();
        $type->setCode($code);
        $type->setVariant($isVariant);
        $type->setLocale('en_US')->setLabel($label);

        $this->persist($type);

        return $type;
    }

    /**
     * @param string|array $data
     *
     * @return Attribute
     */
    protected function createAttribute($data)
    {
        if (is_string($data)) {
            $data = array('code' => $data);
        }

        $data = array_merge(
            array(
                'code'     => null,
                'label'    => null,
                'families' => null,
                'type'     => 'text',
            ),
            $data
        );

        if (isset($data['label']) && !isset($data['label-en_US'])) {
            $data['label-en_US'] = $data['label'];
        }

        $data['code'] = $data['code'] ?: $this->camelize($data['label']);
        unset($data['label']);

        $families = $data['families'];
        unset($data['families']);

        $data['type'] = $this->getAttributeType($data['type']);

        foreach ($data as $key => $element) {
            if (in_array($element, array('yes', 'no'))) {
                $data[$key] = $element === 'yes';
            }
        }

        $attribute = $this->loadFixture('attributes', $data);

        if ($families) {
            foreach ($this->listToArray($families) as $familyCode) {
                $this->getFamily($familyCode)->addAttribute($attribute);
            }
        }

        $this->persist($attribute);

        return $attribute;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getAttributeType($type)
    {
        if (!isset($this->attributeTypes[$type])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Attribute type "%s" is not defined. Please add it in the %s::$attributeTypes property',
                    $type,
                    get_class($this)
                )
            );
        }

        return $this->attributeTypes[$type];
    }

    /**
     * @param string $code
     *
     * @return Category
     */
    protected function createTree($code)
    {
        return $this->createCategory($code);
    }

    /**
     * @param array|string $data
     *
     * @return Category
     */
    protected function createCategory($data)
    {
        if (is_string($data)) {
            $data = array(array('code' => $data));
        }

        $categories = $this->loadFixture('categories', $data);

        /**
         * When using ODM, one must persist and flush category without product
         * before adding and persisting products inside it
         */
        foreach ($categories as $category) {
            $products = $category->getProducts();
            $this->persist($category, true);
            foreach ($products as $product) {
                $product->addCategory($category);
                $this->flush($product);
            }
        }

        return reset($categories);
    }

    /**
     * @param array $data
     *
     * @return Channel
     */
    protected function createChannel($data)
    {
        if (is_string($data)) {
            $data = [['code' => $data]];
        }

        $data = array_merge(
            [
                'label'      => null,
                'color'      => null,
                'currencies' => null,
                'locales'    => null,
                'tree'       => null,
            ],
            $data
        );

        $channel = new Channel();

        $channel->setCode($data['code']);
        $channel->setLabel($data['label']);

        if ($data['color']) {
            $channel->setColor($data['color']);
        }

        foreach ($this->listToArray($data['currencies']) as $currencyCode) {
            $channel->addCurrency($this->getCurrency($currencyCode));
        }

        foreach ($this->listToArray($data['locales']) as $localeCode) {
            $channel->addLocale($this->getLocale($localeCode));
        }

        if ($data['tree']) {
            $channel->setCategory($this->getCategory($data['tree']));
        }

        $this->persist($channel);
    }

    /**
     * @param string $code
     * @param string $label
     * @param string $type
     * @param array  $attributes
     * @param array  $products
     */
    protected function createProductGroup($code, $label, $type, array $attributes, array $products = array())
    {
        $group = new Group();
        $group->setCode($code);
        $group->setLocale('en_US')->setLabel($label); // TODO translation refactoring

        $type = $this->getGroupType($type);
        $group->setType($type);

        foreach ($attributes as $attributeCode) {
            $attribute = $this->getAttribute($attributeCode);
            $group->addAttribute($attribute);
        }

        $this->persist($group);
        $this->flush($group);

        foreach ($products as $sku) {
            if (!empty($sku)) {
                $product = $this->getProduct($sku);
                $product->addGroup($group);
                $this->flush($product);
            }
        }
    }

    /**
     * @param string $code
     * @param string $label
     */
    protected function createAssociationType($code, $label)
    {
        $associationType = new AssociationType();
        $associationType->setCode($code);
        $associationType->setLocale('en_US')->setLabel($label);

        $this->persist($associationType);
    }

    /**
     * @param array $data
     *
     * @return Role
     */
    protected function createRole($data)
    {
        $role = new Role($data['role']);
        $this->persist($role);

        return $role;
    }

    /**
     * Create an attribute option entity
     *
     * @param string $code
     *
     * @return AttributeOption
     */
    protected function createOption($code)
    {
        $option = new AttributeOption();
        $option->setCode($code);

        return $option;
    }

    /**
     * Create a family
     *
     * @param array|string $data
     *
     * @return Family
     */
    protected function createFamily($data)
    {
        if (is_string($data)) {
            $data = array('code' => $data);
        }

        $family = $this->loadFixture('families', $data);

        $this->persist($family);

        return $family;
    }

    /**
     * Create an attribute group
     *
     * @param array|string $data
     *
     * @return AttributeGroup
     */
    protected function createAttributeGroup($data)
    {
        if (is_string($data)) {
            $data = array('code' => $data);
        }

        $attributeGroup = $this->loadFixture('attribute_groups', $data);

        $this->persist($attributeGroup);

        return $attributeGroup;
    }

    /**
     * Load an installer fixture
     *
     * @param string $type
     * @param array  $data
     * @param string $format
     *
     * @return object
     */
    protected function loadFixture($type, array $data, $format = 'csv')
    {
        $processor = $this
            ->getContainer()
            ->get('pim_installer.fixture_loader.configuration_registry')
            ->getProcessor($type, $format);

        $entity = $processor->process($data);

        return $entity;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function camelize($string)
    {
        return Inflector::camelize(str_replace(' ', '_', strtolower($string)));
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getMainContext()->getEntityManager();
    }

    /**
     * @return \Doctrine\Common\Persistence\ManagerRegistry
     */
    protected function getSmartRegistry()
    {
        return $this->getMainContext()->getSmartRegistry();
    }

    /**
     * @param string $namespace
     *
     * @return Repository
     */
    protected function getRepository($namespace)
    {
        return $this->getSmartRegistry()->getManagerForClass($namespace)->getRepository($namespace);
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Manager\ProductManager
     */
    protected function getProductManager()
    {
        return $this->getContainer()->get('pim_catalog.manager.product');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Builder\ProductBuilder
     */
    protected function getProductBuilder()
    {
        return $this->getContainer()->get('pim_catalog.builder.product');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Manager\AttributeManager
     */
    protected function getAttributeManager()
    {
        return $this->getContainer()->get('pim_catalog.manager.attribute');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Manager\MediaManager
     */
    protected function getMediaManager()
    {
        return $this->getContainer()->get('pim_catalog.manager.media');
    }

    /**
     * @return \Gaufrette\Filesystem
     */
    protected function getPimFilesystem()
    {
        return $this->getContainer()->get('pim_filesystem');
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getMainContext()->getContainer();
    }

    /**
     * @param string $list
     *
     * @return array
     */
    protected function listToArray($list)
    {
        return $this->getMainContext()->listToArray($list);
    }

    /**
     * @param object $object
     */
    protected function refresh($object)
    {
        if (is_object($object)) {
            $this->getSmartRegistry()->getManagerForClass(get_class($object))->refresh($object);
        }
    }

    /**
     * @param object  $object
     * @param boolean $flush
     */
    protected function persist($object, $flush = true)
    {
        $manager = $this->getSmartRegistry()->getManagerForClass(get_class($object));
        $manager->persist($object);

        if ($flush) {
            $manager->flush($object);
        }
    }

    /**
     * @param object  $object
     * @param boolean $flush
     */
    protected function remove($object, $flush = true)
    {
        $manager = $this->getSmartRegistry()->getManagerForClass(get_class($object));
        $manager->remove($object);

        if ($flush) {
            $manager->flush($object);
        }
    }

    /**
     * @param object $object
     *
     * @return null
     */
    protected function flush($object = null)
    {
        if (!$object) {
            return $this->flushAll();
        }

        $manager = $this->getSmartRegistry()->getManagerForClass(get_class($object));
        $manager->flush($object);
    }

    /**
     * Flush all managers
     */
    protected function flushAll()
    {
        foreach ($this->getSmartRegistry()->getManagers() as $manager) {
            $manager->flush();
        }
    }
}
