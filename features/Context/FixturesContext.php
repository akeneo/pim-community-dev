<?php

namespace Context;

use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Util\Inflector;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Gherkin\Node\PyStringNode;
use Oro\Bundle\BatchBundle\Entity\JobInstance;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\CatalogBundle\Entity\Association;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
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
    private $locales = array(
        'english' => 'en_US',
        'french'  => 'fr_FR',
        'german'  => 'de_DE',
    );

    private $attributeTypes = array(
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

    private $entities = array(
        'Attribute'       => 'PimCatalogBundle:ProductAttribute',
        'AttributeGroup'  => 'PimCatalogBundle:AttributeGroup',
        'AttributeOption' => 'PimCatalogBundle:AttributeOption',
        'Channel'         => 'PimCatalogBundle:Channel',
        'Currency'        => 'PimCatalogBundle:Currency',
        'Family'          => 'PimCatalogBundle:Family',
        'Category'        => 'PimCatalogBundle:Category',
        'Association'     => 'PimCatalogBundle:Association',
        'JobInstance'     => 'OroBatchBundle:JobInstance',
        'User'            => 'OroUserBundle:User',
        'Role'            => 'OroUserBundle:Role',
        'Locale'          => 'PimCatalogBundle:Locale',
        'GroupType'       => 'PimCatalogBundle:GroupType',
        'Product'         => 'Pim\Bundle\CatalogBundle\Model\Product',
        'ProductGroup'    => 'Pim\Bundle\CatalogBundle\Entity\Group',
    );

    private $placeholderValues = array();

    /**
     * @BeforeScenario
     */
    public function resetPlaceholderValues()
    {
        $this->placeholderValues = array();
    }

    /**
     * @BeforeScenario
     */
    public function clearUOW()
    {
        $this->getEntityManager()->clear();
    }

    /**
     * @BeforeScenario
     */
    public function clearPimFilesystem()
    {
        $fs = $this->getPimFilesystem();
        foreach ($fs->keys() as $key) {
            $fs->delete($key);
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

        $namespace = $this->entities[$entityName];

        return $this->getRepository($namespace)->findOneBy($criteria);
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
     * @param string         $sku
     * @param TableNode|null $translations
     *
     * @return Product
     * @Given /^a "([^"]*)" product$/
     */
    public function aProduct($sku, TableNode $translations = null)
    {
        $product = $this->getOrCreateProduct($sku);

        $this->getProductManager()->save($product);

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
            $product = $this->aProduct($data['sku']);

            if (!empty($data['family'])) {
                $product->setFamily($this->getFamily($data['family']));
            }

            if (isset($data['enabled'])) {
                $product->setEnabled($data['enabled'] === 'yes');
            }

            if (isset($data['categories'])) {
                $categoryCodes = $this->listToArray($data['categories']);
                foreach ($categoryCodes as $code) {
                    $category = $this->getCategory($code);
                    $product->addCategory($category);
                }
            }

            $this->getProductManager()->save($product);
        }
    }

    /**
     * @param string $status
     * @param string $sku
     *
     * @Given /^(?:an|a) (enabled|disabled) "([^"]*)" product$/
     */
    public function anEnabledOrDisabledProduct($status, $sku)
    {
        $product = $this->aProduct($sku);
        $product->setEnabled($status === 'enabled');
        $this->persist($product);
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following famil(?:y|ies):$/
     */
    public function theFollowingFamilies(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $data = array_merge(
                array(
                    'locale' => 'english'
                ),
                $data
            );

            $family = $this->createFamily($data['code']);
            if (isset($data['label'])) {
                $family
                    ->setLocale($this->getLocaleCode($data['locale']))
                    ->setLabel($data['label']);
            }
            $this->persist($family, false);
        }

        $this->flush();
    }

    /**
     * @param string    $family
     * @param TableNode $table
     *
     * @Given /^the family "([^"]*)" has the following attributes?:$/
     */
    public function theFamilyHasTheFollowingAttribute($family, TableNode $table)
    {
        $family = $this->getFamily($family);

        foreach ($table->getHash() as $data) {
            $code = $this->camelize($data['label']);
            $attribute = $this->getAttribute($code);
            $family->addAttribute($attribute);
            if ('yes' === $data['attribute as label']) {
                $family->setAttributeAsLabel($attribute);
            }
        }

        $this->flush();
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
            $data = array_merge(
                array(
                    'locale' => 'english'
                ),
                $data
            );

            $group = $this->findAttributeGroup($data['code']);

            if (!$group) {
                $group = new AttributeGroup();
                $group->setSortOrder($index);
                $group->setCode($data['code']);
            }

            $group
                ->setLocale($this->getLocaleCode($data['locale']))
                ->setLabel($data['label']);

            $this->persist($group);
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
    public function theFollowingProductValue(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $data['locale'] = empty($data['locale']) ? null : $this->getLocale($data['locale'])->getCode();
            $data['scope']  = empty($data['scope']) ? null : $this->getChannel($data['scope'])->getCode();

            $product = $this->getProduct($data['product']);
            $value   = $product->getValue($this->camelize($data['attribute']), $data['locale'], $data['scope']);

            if ($value) {
                if ($data['scope']) {
                    $value->setScope($data['scope']);
                }
                if ($data['locale']) {
                    $value->setLocale($data['locale']);
                }
                if ($data['value']) {
                    if ($value->getAttribute()->getAttributeType() === $this->attributeTypes['prices']) {
                        $prices = $this->listToPrices($data['value']);
                        foreach ($prices as $currency => $data) {
                            $value->getPrice($currency)->setData($data);
                        }
                    } elseif ($value->getAttribute()->getAttributeType() === $this->attributeTypes['simpleselect']) {
                        $options = $value->getAttribute()->getOptions();
                        $optionValue = null;
                        foreach ($options as $option) {
                            if ((string) $option->getCode() === $data['value']) {
                                $optionValue = $option;
                            }
                        }

                        if ($optionValue === null) {
                            throw new \InvalidArgumentException(sprintf('Unknown option value "%s"', $data['value']));
                        }

                        $value->setData($optionValue);
                    } elseif ($value->getAttribute()->getAttributeType() === $this->attributeTypes['metric']) {
                        $metric = $value->getData();

                        if (false === strpos($data['value'], ' ')) {
                            throw new \InvalidArgumentException(
                                sprintf(
                                    'Metric value does not match expected format "<data> <unit>": %s',
                                    $data['value']
                                )
                            );
                        }
                        list($data, $unit) = explode(' ', $data['value']);

                        if (!$metric) {
                            $metric = new Metric();
                            $metric->setFamily($value->getAttribute()->getMetricFamily());
                        }

                        $metric->setData($data);
                        $metric->setUnit($unit);

                        $value->setMetric($metric);
                    } else {
                        $value->setData($data['value']);
                    }
                }
            } else {
                $code = $this->camelize($data['attribute']);
                $attribute = $this->findAttribute($code);
                if (!$attribute) {
                    $code = implode('_', explode(' ', strtolower($data['attribute'])));
                    $attribute = $this->getAttribute($code);
                }
                $value = $this->createValue($attribute, $data['value'], $data['locale'], $data['scope']);
                $product->addValue($value);
            }
            $this->getProductManager()->save($product);
        }

        $this->flush();
    }

    /**
     * @param string $attribute
     * @param string $family
     *
     * @Given /^the attribute "([^"]*)" has been removed from the "([^"]*)" family$/
     */
    public function theAttributeHasBeenRemovedFromTheFamily($attribute, $family)
    {
        $code      = $this->camelize($attribute);
        $attribute = $this->getAttribute($code);
        $family    = $this->getFamily($family);

        $family->removeAttribute($attribute);

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
            $category = $this->createCategory($data['code']);
            $category->setLocale('en_US')->setLabel($data['label']); // TODO translation refactoring

            if (!empty($data['parent'])) {
                $parent = $this->getOrCreateCategory($data['parent']);
                $category->setParent($parent);
            }

            if (isset($data['products']) && trim($data['products']) != '') {
                $skus = explode(',', $data['products']);
                foreach ($skus as $sku) {
                    $category->addProduct($this->getOrCreateProduct(trim($sku)));
                }
            }

            $this->persist($category);
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
            $this->getEntityManager()->refresh($attribute);

            assertEquals($data['label-en_US'], $attribute->getTranslation('en_US')->getLabel());
            assertEquals($this->getAttributeType($data['type']), $attribute->getAttributeType());
            assertEquals(($data['translatable'] == 1), $attribute->isTranslatable());
            assertEquals(($data['scopable'] == 1), $attribute->isScopable());
            assertEquals($data['group'], $attribute->getGroup()->getCode());
            assertEquals(($data['useable_as_grid_column'] == 1), $attribute->isUseableAsGridColumn());
            assertEquals(($data['useable_as_grid_filter'] == 1), $attribute->isUseableAsGridFilter());
            assertEquals(($data['unique'] == 1), $attribute->isUnique());
            if ($data['allowed_extensions'] != '') {
                assertEquals(explode(',', $data['allowed_extensions']), $attribute->getAllowedExtensions());
            }
            assertEquals($data['date_type'], $attribute->getDateType());
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
            $this->getEntityManager()->refresh($option);

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
            $this->getEntityManager()->refresh($category);

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
     * @Then /^there should be the following associations:$/
     */
    public function thereShouldBeTheFollowingAssociations(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $association = $this->getAssociation($data['code']);
            $this->getEntityManager()->refresh($association);

            assertEquals($data['label-en_US'], $association->getTranslation('en_US')->getLabel());
            assertEquals($data['label-fr_FR'], $association->getTranslation('fr_FR')->getLabel());
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
            $this->getEntityManager()->refresh($group);

            assertEquals($data['label-en_US'], $group->getTranslation('en_US')->getLabel());
            assertEquals($data['label-fr_FR'], $group->getTranslation('fr_FR')->getLabel());
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
            $data = array_merge(
                array(
                    'label'      => null,
                    'locales'    => null,
                    'currencies' => null,
                    'category'   => null,
                ),
                $data
            );

            $category = null;
            if ($data['category']) {
                $category = $this->getCategory($data['category']);
            }

            try {
                $channel = $this->getChannel($data['code']);

                if ($data['label']) {
                    $channel->setLabel($data['label']);
                }

                if ($category !== null) {
                    $channel->setCategory($category);
                }

                if ($data['locales']) {
                    foreach ($this->listToArray($data['locales']) as $localeCode) {
                        $channel->addLocale($this->getLocale($localeCode));
                    }
                }

                if ($data['currencies']) {
                    foreach ($this->listToArray($data['currencies']) as $currencyCode) {
                        $channel->addCurrency($this->getCurrency($currencyCode));
                    }
                }

                $this->persist($channel);
            } catch (\InvalidArgumentException $e) {
                $this->createChannel(
                    $data['code'],
                    $data['label'],
                    $this->listToArray($data['locales']),
                    $category,
                    $this->listToArray($data['currencies'])
                );
            }
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
        $registry = $this->getContainer()->get('oro_batch.connectors');

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
     * @Given /^the following attribute requirements:$/
     */
    public function theFollowingAttributeRequirements(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $requirement = new AttributeRequirement();

            $attribute = $this->getAttribute($data['attribute']);
            $channel   = $this->getChannel($data['scope']);
            $family    = $this->getFamily($data['family']);

            $requirement->setAttribute($attribute);
            $requirement->setChannel($channel);
            $requirement->setFamily($family);

            $requirement->setRequired($data['required'] === 'yes');

            $this->persist($requirement, false);
        }

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
     * @Given /^the following associations?:$/
     */
    public function theFollowingAssociations(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $code = $data['code'];
            $label = isset($data['label']) ? $data['label'] : null;

            $this->createAssociation($code, $label);
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
            $isVariant = isset($data['is_variant']) ? $data['is_variant'] : 0;

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
        foreach ($this->listToArray($products) as $identifier) {
            $productValue = $this->getProductValue($identifier, strtolower($attribute));

            foreach ($table->getHash() as $price) {
                assertEquals($price['amount'], $productValue->getPrice($price['currency'])->getData());
            }
        }
    }

    /**
     * @param string    $attribute
     * @param string    $products
     * @param TableNode $table
     *
     * @return null
     * @Given /^the options "([^"]*)" of products (.*) should be:$/
     */
    public function theOptionsOfProductsShouldBe($attribute, $products, TableNode $table)
    {
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
        foreach ($this->listToArray($products) as $identifier) {
            $productValue = $this->getProductValue($identifier, strtolower($attribute));
            $media = $productValue->getMedia();
            $this->getEntityManager()->refresh($media);
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
        $this->placeholderValues['file to import'] = $filename =
            sprintf('/tmp/pim-import/behat-import-%s.csv', substr(md5(rand()), 0, 7));
        @rmdir(dirname($filename));
        @mkdir(dirname($filename), 0777, true);

        file_put_contents($filename, (string) $string);
    }

    /**
     * @param TableNode $table
     *
     * @return null
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
     * @Given /^import directory of "([^"]*)" contain the following media:$/
     */
    public function importDirectoryOfContainTheFollowingMedia($code, TableNode $table)
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
        $total = count($this->getProductManager()->getFlexibleRepository()->findAll());

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
        $this->getEntityManager()->refresh($product);

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
     * @Given /^(?:the )?categor(?:y|ies) of "([^"]*)" should be "([^"]*)"$/
     */
    public function theCategoriesOfShouldBe($productCode, $categoryCodes)
    {
        $categories = $this->getProduct($productCode)->getCategories();
        if (!$categories) {
            if (!$categoryCodes) {
                return;
            } else {
                throw \Exception(sprintf('Product "%s" doesn\'t belong to any categories', $productCode));
            }
        }
        $categories = $categories->map(
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
     * @Then /^"([^"]*)" group should contain "([^"]*)"$/
     */
    public function groupShouldContain($group, $products)
    {
        $group = $this->getProductGroup($group);
        $this->getEntityManager()->refresh($group);
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

        return $product;
    }

    /**
     * @param string $username
     *
     * @return User
     * @Then /^there should be a "([^"]*)" user$/
     */
    public function getUser($username)
    {
        return $this->getEntityOrException('User', array('username' => $username));
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
        if (false !== strpos($value, '{{') && false !== strpos($value, '}}')) {
            $key = trim(str_replace(array('{{', '}}'), '', $value));
            if (array_key_exists($key, $this->placeholderValues)) {
                return $this->placeholderValues[$key];
            }
        }

        return $value;
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
    private function getProductValue($identifier, $attribute, $locale = null, $scope = null)
    {
        $product = $this->getProduct($identifier);

        $this->getEntityManager()->refresh($product);

        $values = $product->getValues()->filter(
            function ($value) use ($attribute, $locale, $scope) {
                return $value->isMatching($attribute, $locale, $scope);
            }
        );

        if (!$values->count()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not find product value for attribute "%s" in locale "%s" for scope "%s"',
                    $attribute,
                    $locale,
                    $scope
                )
            );
        }

        if ($values->count() > 1) {
            throw new \Exception(
                sprintf(
                    '"%s": expecting to see only one value for attribute "%s" in locale "%s" for scope "%s", found %d',
                    $identifier,
                    $attribute,
                    $locale,
                    $scope,
                    $values->count()
                )
            );
        }

        return $values->first();
    }

    /**
     * @param string  $code
     * @param string  $label
     * @param boolean $isVariant
     *
     * @return GroupType
     */
    private function createGroupType($code, $label, $isVariant)
    {
        $type = new GroupType();
        $type->setCode($code);
        $type->setVariant($isVariant);
        $type->setLocale('en_US')->setLabel($label);

        $this->persist($type);

        return $type;
    }

    /**
     * @param string $sku
     *
     * @return Product
     */
    private function createProduct($sku)
    {
        $product = $this->getProductManager()->createFlexible();

        $product->getIdentifier()->setData($sku);

        $this->getProductManager()->save($product);

        return $product;
    }

    /**
     * @param array $data
     *
     * @return ProductAttribute
     */
    private function createAttribute($data)
    {
        $data = array_merge(
            array(
                'code'                   => null,
                'label'                  => null,
                'position'               => 0,
                'group'                  => null,
                'families'               => null,
                'type'                   => 'text',
                'scopable'               => 'no',
                'translatable'           => 'no',
                'useable as grid column' => false,
                'useable as grid filter' => false,
            ),
            $data
        );

        if ('identifier' === $data['type']) {
            throw new \InvalidArgumentException('Identifier attribute can\'t be created after catalog initialization');
        }

        $data['code'] = $data['code'] ?: $this->camelize($data['label']);

        $attribute = $this->getProductAttributeManager()->createAttribute($this->getAttributeType($data['type']));

        $attribute->setCode($data['code']);
        $attribute->setLocale('en_US')->setLabel($data['label']); //TODO translation refactoring

        $attribute->setScopable(strtolower($data['scopable']) === 'yes');
        $attribute->setTranslatable(strtolower($data['translatable']) === 'yes');
        $attribute->setUseableAsGridColumn(strtolower($data['useable as grid column']) === 'yes');
        $attribute->setUseableAsGridFilter(strtolower($data['useable as grid filter']) === 'yes');

        $attribute->setSortOrder($data['position']);

        if ($data['group']) {
            $attribute->setGroup($this->getAttributeGroup($data['group']));
        }

        if ($data['families']) {
            foreach ($this->listToArray($data['families']) as $familyCode) {
                $this->getFamily($familyCode)->addAttribute($attribute);
            }
        }

        if ($data['type'] === 'metric') {
            if (!empty($data['metric family']) && !empty($data['default metric unit'])) {
                $attribute->setMetricFamily($data['metric family']);
                $attribute->setDefaultMetricUnit($data['default metric unit']);
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expecting "metric family" and "default metric unit" columns to be defined for attribute "%s"',
                        $data['label']
                    )
                );
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
    private function getAttributeType($type)
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
     * @param ProductAttribute $attribute
     * @param mixed            $data
     * @param string           $locale
     * @param string           $scope
     *
     * @return ProductValue
     */
    private function createValue(ProductAttribute $attribute, $data = null, $locale = null, $scope = null)
    {
        $manager = $this->getProductManager();

        $value = $manager->createFlexibleValue();
        $value->setAttribute($attribute);

        switch ($attribute->getAttributeType()) {
            case $this->attributeTypes['prices']:
                $prices = $this->listToPrices($data);
                foreach ($prices as $currency => $data) {
                    $value->addPrice($this->createPrice($data, $currency));
                }
                break;

            case $this->attributeTypes['image']:
            case $this->attributeTypes['file']:
                $media = $this->createMedia($data);
                $value->setMedia($media);
                break;

            case $this->attributeTypes['simpleselect']:
            case $this->attributeTypes['multiselect']:
                $options = $attribute->getOptions()->filter(
                    function ($option) use ($data) {
                        return $option->getCode() == $data;
                    }
                );

                if (empty($options)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Could not find option "%s" for attribute "%s"',
                            $data,
                            (string) $attribute
                        )
                    );
                }
                $option = $options->first();

                if ($option) {
                    if ($attribute->getAttributeType() === $this->attributeTypes['simpleselect']) {
                        $value->setOption($option);
                    } else {
                        $value->addOption($option);
                    }
                }
                break;

            case $this->attributeTypes['metric']:
                list($data, $unit) = explode(' ', $data);
                $metric = new Metric();
                $metric->setFamily($attribute->getMetricFamily());
                $metric->setData($data);
                $metric->setUnit($unit);
                $value->setData($metric);
                break;

            default:
                $value->setData($data);
        }
        $value->setLocale($locale);
        $value->setScope($scope);

        return $value;
    }

    /**
     * @param string $code
     *
     * @return Category
     */
    private function createTree($code)
    {
        return $this->createCategory($code);
    }

    /**
     * @param string $code
     *
     * @return Category
     */
    private function createCategory($code)
    {
        $category = new Category();
        $category->setCode($code);
        $this->persist($category);

        return $category;
    }

    /**
     * @param string   $code
     * @param string   $label
     * @param string[] $locales
     * @param Category $tree
     * @param string[] $currencies
     */
    private function createChannel($code, $label = null, $locales = array(), $tree = null, $currencies = array())
    {
        $channel = new Channel();
        $channel->setCode($code);

        if ($label === null) {
            $label = ucfirst($code);
        }
        $channel->setLabel($label);

        if ($tree !== null) {
            $channel->setCategory($tree);
        }

        foreach ($locales as $localeCode) {
            $channel->addLocale($this->getLocale($localeCode));
        }

        foreach ($currencies as $currencyCode) {
            $channel->addCurrency($this->getCurrency($currencyCode));
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
    private function createProductGroup($code, $label, $type, array $attributes, array $products = array())
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

        foreach ($products as $sku) {
            if (!empty($sku)) {
                $product = $this->getProduct($sku);
                $group->addProduct($product);
                $product->addGroup($group);
            }
        }

        $this->persist($group);
    }

    /**
     * @param string $code
     * @param string $label
     */
    private function createAssociation($code, $label)
    {
        $association = new Association();
        $association->setCode($code);
        $association->setLocale('en_US')->setLabel($label);

        $this->persist($association);
    }

    /**
     * @param array $data
     *
     * @return Role
     */
    private function createRole($data)
    {
        $role = new Role($data['role']);
        $this->persist($role);

        return $role;
    }

    /**
     * @param string $prices
     *
     * @return array
     */
    private function listToPrices($prices)
    {
        $prices = explode(',', $prices);
        $data = array();

        foreach ($prices as $price) {
            $price = explode(' ', trim($price));
            $amount = array_filter(
                $price,
                function ($item) {
                    return preg_match('/^[0-9]+(\.[0-9]+)?$/', $item);
                }
            );
            $amount = reset($amount);
            if (!$amount) {
                continue;
            }
            $currency = array_filter(
                $price,
                function ($item) {
                    return preg_match('/^[a-zA-Z]+(.+)$/', $item);
                }
            );
            $currency = !empty($currency) ? reset($currency) : 'EUR';
            $data[$currency] = $amount;
        }

        return $data;
    }

    /**
     * @param string $file
     *
     * @return Media
     */
    private function createMedia($file)
    {
        $media = new Media();
        if ($file) {
            $media->setFile(new File(__DIR__ . '/fixtures/' . $file));
            $this->getMediaManager()->handle($media, 'behat');
        }

        return $media;
    }

    /**
     * @param string $data
     * @param string $currency
     *
     * @return ProductPrice
     */
    private function createPrice($data, $currency = 'EUR')
    {
        $price = new ProductPrice();
        $price->setData($data);
        $price->setCurrency($currency);

        return $price;
    }

    /**
     * Create an attribute option entity
     *
     * @param string $code
     *
     * @return AttributeOption
     */
    private function createOption($code)
    {
        $option = new AttributeOption();
        $option->setCode($code);

        return $option;
    }

    /**
     * Create a family
     *
     * @param string $code
     *
     * @return Family
     */
    private function createFamily($code)
    {
        $family = $this->getFamilyFactory()->createFamily();
        $family->setCode($code);

        $this->persist($family);

        return $family;
    }


    /**
     * @param string $string
     *
     * @return string
     */
    private function camelize($string)
    {
        return Inflector::camelize(str_replace(' ', '_', strtolower($string)));
    }

    /**
     * Persist an entity
     *
     * @param object  $entity
     * @param boolean $flush
     */
    private function persist($entity, $flush = true)
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * Remove an entity
     *
     * @param object  $entity
     * @param boolean $flush
     */
    private function remove($entity, $flush = true)
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * Flush
     */
    private function flush()
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->getMainContext()->getEntityManager();
    }

    /**
     * @param string $repository
     *
     * @return Repository
     */
    private function getRepository($repository)
    {
        return $this->getEntityManager()->getRepository($repository);
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Manager\ProductManager
     */
    private function getProductManager()
    {
        return $this->getContainer()->get('pim_catalog.manager.product');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Manager\ProductAttributeManager
     */
    private function getProductAttributeManager()
    {
        return $this->getContainer()->get('pim_catalog.manager.product_attribute');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Manager\MediaManager
     */
    private function getMediaManager()
    {
        return $this->getContainer()->get('pim_catalog.manager.media');
    }

    /**
     * @return \Gaufrette\Filesystem
     */
    private function getPimFilesystem()
    {
        return $this->getContainer()->get('pim_filesystem');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Factory\FamilyFactory
     */
    private function getFamilyFactory()
    {
        return $this->getContainer()->get('pim_catalog.factory.family');
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private function getContainer()
    {
        return $this->getMainContext()->getContainer();
    }

    /**
     * @param string $list
     *
     * @return array
     */
    private function listToArray($list)
    {
        return $this->getMainContext()->listToArray($list);
    }
}
