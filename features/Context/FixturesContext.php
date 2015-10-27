<?php

namespace Context;

use Acme\Bundle\AppBundle\Entity\Color;
use Acme\Bundle\AppBundle\Entity\Fabric;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Util\Inflector;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CommentBundle\Entity\Comment;
use Pim\Bundle\CommentBundle\Model\CommentInterface;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Bundle\UserBundle\Entity\User;
use Pim\Component\Connector\Processor\Denormalization\ProductProcessor;
use Pim\Component\ReferenceData\Model\ReferenceDataInterface;

/**
 * A context for creating entities
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FixturesContext extends RawMinkContext
{
    protected $locales = [
        'english'    => 'en_US',
        'french'     => 'fr_FR',
        'german'     => 'de_DE',
        'english UK' => 'en_GB',
        'spanish'    => 'es_ES',
        'italian'    => 'it_IT',
        'portuguese' => 'pt_PT',
        'russian'    => 'ru_RU',
        'japanese'   => 'ja_JP'
    ];

    protected $attributeTypes = [
        'text'                        => 'pim_catalog_text',
        'number'                      => 'pim_catalog_number',
        'textarea'                    => 'pim_catalog_textarea',
        'identifier'                  => 'pim_catalog_identifier',
        'metric'                      => 'pim_catalog_metric',
        'prices'                      => 'pim_catalog_price_collection',
        'image'                       => 'pim_catalog_image',
        'file'                        => 'pim_catalog_file',
        'multiselect'                 => 'pim_catalog_multiselect',
        'simpleselect'                => 'pim_catalog_simpleselect',
        'date'                        => 'pim_catalog_date',
        'boolean'                     => 'pim_catalog_boolean',
        'reference_data_simpleselect' => 'pim_reference_data_simpleselect',
        'reference_data_multiselect'  => 'pim_reference_data_multiselect',
    ];

    protected $entities = [
        'Attribute'        => 'PimCatalogBundle:Attribute',
        'AttributeGroup'   => 'PimCatalogBundle:AttributeGroup',
        'AttributeOption'  => 'PimCatalogBundle:AttributeOption',
        'Channel'          => 'PimCatalogBundle:Channel',
        'Currency'         => 'PimCatalogBundle:Currency',
        'Family'           => 'PimCatalogBundle:Family',
        'Category'         => 'PimCatalogBundle:Category', // TODO: To remove
        'ProductCategory'  => 'PimCatalogBundle:Category',
        'AssociationType'  => 'PimCatalogBundle:AssociationType',
        'JobInstance'      => 'AkeneoBatchBundle:JobInstance',
        'JobConfiguration' => 'Pim\Component\Connector\Model\JobConfiguration',
        'User'             => 'PimUserBundle:User',
        'Role'             => 'OroUserBundle:Role',
        'UserGroup'        => 'OroUserBundle:Group',
        'Locale'           => 'PimCatalogBundle:Locale',
        'GroupType'        => 'PimCatalogBundle:GroupType',
        'Product'          => 'Pim\Bundle\CatalogBundle\Model\Product',
        'ProductGroup'     => 'Pim\Bundle\CatalogBundle\Entity\Group',
    ];

    protected $placeholderValues = [];

    protected $username;

    /**
     * @BeforeScenario
     */
    public function resetPlaceholderValues()
    {
        $this->placeholderValues = [
            '%tmp%'      => getenv('BEHAT_TMPDIR') ?: '/tmp/pim-behat',
            '%fixtures%' => __DIR__ . '/fixtures'
        ];
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
        foreach ($this->getPimFilesystems() as $fs) {
            foreach ($fs->listContents() as $key) {
                if ('dir' === $key['type']) {
                    $fs->deleteDir($key['path']);
                } else {
                    $fs->delete($key['path']);
                }
            }
        }
    }

    /**
     * Magic methods for getting and creating entities
     *
     * @param string $method
     * @param array  $args
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
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
            $getter     = null;
            $entityName = null;
        }

        if ($getter && array_key_exists($entityName, $this->getEntities())) {
            $method = $getter . 'Entity';

            return $this->$method($entityName, $args[0]);
        }

        throw new \BadMethodCallException(sprintf('There is no method named %s in FixturesContext', $method));
    }

    /**
     * @param string $entityName
     * @param mixed  $data
     *
     * @throws \InvalidArgumentException If entity is not found
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
     * @throws \Exception
     *
     * @return null|object
     */
    public function findEntity($entityName, $criteria)
    {
        if (!array_key_exists($entityName, $this->getEntities())) {
            throw new \Exception(sprintf('Unrecognized entity "%s".', $entityName));
        }

        if (gettype($criteria) === 'string' || $criteria === null) {
            $criteria = ['code' => $criteria];
        }

        return $this->getRepository($this->getEntities()[$entityName])->findOneBy($criteria);
    }

    /**
     * @param string $entityName
     * @param mixed  $criteria
     *
     * @throws \InvalidArgumentException If entity is not found
     *
     * @return object
     */
    public function getEntityOrException($entityName, $criteria)
    {
        $entity = $this->findEntity($entityName, $criteria);

        if (!$entity) {
            if (is_string($criteria)) {
                $criteria = ['code' => $criteria];
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Could not find "%s" with criteria %s',
                    $this->getEntities()[$entityName],
                    print_r(\Doctrine\Common\Util\Debug::export($criteria, 2), true)
                )
            );
        }

        return $entity;
    }

    /**
     * @param array|string $data
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     *
     * @Given /^a "([^"]*)" product$/
     */
    public function createProduct($data)
    {
        if (is_string($data)) {
            $data = ['sku' => $data];
        } elseif (isset($data['enabled']) && in_array($data['enabled'], ['yes', 'no'])) {
            $data['enabled'] = ($data['enabled'] === 'yes');
        }

        foreach ($data as $key => $value) {
            $data[$key] = $this->replacePlaceholders($value);
        }

        // use the processor part of the import system
        $product = $this->loadFixture('products', $data);
        $this->getProductSaver()->save($product, ['recalculate' => false]);

        // reset the unique value set to allow to update product values
        $uniqueValueSet = $this->getContainer()->get('pim_catalog.validator.unique_value_set');
        $uniqueValueSet->reset();

        return $product;
    }

    /**
     * @param array|string $data
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Group
     *
     * @Given /^a "([^"]*)" variant group$/
     */
    public function createVariantGroup($data)
    {
        if (is_string($data)) {
            $data = ['code' => $data];
        }
        $variantGroup = $this->loadFixture('variant_groups', $data);
        $this->saveVariantGroup($variantGroup);

        return $variantGroup;
    }

    /**
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Model\GroupInterface
     */
    protected function getVariantGroup($code)
    {
        $repository = $this->getContainer()->get('pim_catalog.repository.group');
        $group      = $repository->findOneByCode($code);

        return $group;
    }

    /**
     * @param Group $group
     */
    protected function saveVariantGroup(Group $group)
    {
        $saver = $this->getContainer()->get('pim_catalog.saver.group');
        $saver->save($group);
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
     * @param TableNode $table
     *
     * @Given /^the product?:$/
     */
    public function theProduct(TableNode $table)
    {
        $this->createProduct(
            $table->getRowsHash()
        );
    }

    /**
     * @param string $status
     * @param string $sku
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     *
     * @Given /^(?:an|a) (enabled|disabled) "([^"]*)" product$/
     */
    public function anEnabledOrDisabledProduct($status, $sku)
    {
        return $this->createProduct(
            [
                'sku'     => $sku,
                'enabled' => $status === 'enabled' ? 'yes' : 'no'
            ]
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
     *
     * @throws \Exception
     */
    public function thereIsNoEntity($entityName)
    {
        if (strpos($entityName, ' ')) {
            $entityName = implode('', array_map('ucfirst', explode(' ', $entityName)));
        }

        $entityName = ucfirst($entityName);

        if (!array_key_exists($entityName, $this->getEntities())) {
            throw new \Exception(sprintf('Unrecognized entity "%s".', $entityName));
        }

        $namespace = $this->getEntities()[$entityName];
        $entities  = $this->getRepository($namespace)->findAll();

        foreach ($entities as $entity) {
            // TODO use a Remover
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
        // TODO use a Saver
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
            $attribute = $this->getAttribute($data['attribute']);
            $attribute->setLocale($this->getLocaleCode($data['locale']))->setLabel($data['label']);
            $this->validate($attribute);
            $this->persist($attribute);
        }
        // TODO use a Saver
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
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following variant group values?:$/
     */
    public function theFollowingVariantGroupValues(TableNode $table)
    {
        $groups = [];

        foreach ($table->getHash() as $row) {
            $row = array_merge(['locale' => null, 'scope' => null, 'value' => null], $row);

            $attributeCode = $row['attribute'];
            if ($row['locale']) {
                $attributeCode .= '-' . $row['locale'];
            }
            if ($row['scope']) {
                $attributeCode .= '-' . $row['scope'];
            }
            $groups[$row['group']][$attributeCode] = $this->replacePlaceholders($row['value']);
        }

        foreach ($groups as $code => $data) {
            if (!isset($data['type'])) {
                $data['type'] = 'VARIANT';
            }
            $this->createVariantGroup(['code' => $code] + $data);
        }
        // TODO use a Saver
        $this->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following product comments:$/
     */
    public function theFollowingProductComments(TableNode $table)
    {
        $comments = [];

        foreach ($table->getHash() as $row) {
            $product             = $this->getProductRepository()->findOneByIdentifier($row['product']);
            $row['resource']     = $product;
            $comments[$row['#']] = $this->createComment($row, $comments);
        }
        // TODO use a Saver
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
        $this->validate($product);
        $this->getProductSaver()->save($product);
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

        $this->validate($family);
        $this->getFamilySaver()->save($family);
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following categor(?:y|ies):$/
     */
    public function theFollowingCategories(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->createCategory($data);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following datagrid views:$/
     */
    public function theFollowingDatagridViews(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->createDatagridView($data);
        }
    }

    /**
     * @param int    $attributeCount
     * @param string $filterable
     * @param string $type
     * @param int    $optionCount
     *
     * @Given /^(\d+) (filterable )?(simple|multi) select attributes with (\d+) options per attribute$/
     */
    public function createSelectAttributesWithOptions($attributeCount, $filterable, $type, $optionCount)
    {
        $attCodePattern     = 'attribute_%d';
        $attLabelPattern    = 'Attribute %d';
        $optionCodePattern  = 'attribute_%d_option_%d';
        $optionLabelPattern = 'Option %d for attribute %d';

        $attributeConfig = [
            'type'                   => $this->getAttributeType($type . 'select'),
            'group'                  => 'other',
            'useable_as_grid_filter' => (bool) $filterable
        ];

        $attributeData = [];
        $optionData    = [];

        for ($i = 1; $i <= $attributeCount; $i++) {
            $attributeData[] = [
                'code'        => sprintf($attCodePattern, $i),
                'label-en_US' => sprintf($attLabelPattern, $i),
            ] + $attributeConfig;

            for ($j = 1; $j <= $optionCount; $j++) {
                $optionData[] = [
                    'attribute'   => sprintf($attCodePattern, $i),
                    'code'        => sprintf($optionCodePattern, $i, $j),
                    'label-en_US' => sprintf($optionLabelPattern, $j, $i),
                    'label-fr_FR' => sprintf($optionLabelPattern, $j, $i)
                ];
            }
        }

        foreach ($attributeData as $index => $data) {
            $attribute = $this->loadFixture('attributes', $data);
            $this->validate($attribute);
            $this->persist($attribute, $index % 200 === 0);
        }
        // TODO use a Saver
        $this->flush();

        foreach ($optionData as $index => $data) {
            $option = $this->loadFixture('attribute_options', $data);
            $this->validate($option);
            $this->persist($option, $index % 200 === 0);
        }
        // TODO use a Saver
        $this->flush();
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
            assertEquals(($data['useable_as_grid_filter'] == 1), $attribute->isUseableAsGridFilter());
            assertEquals(($data['unique'] == 1), $attribute->isUnique());
            if ($data['allowed_extensions'] != '') {
                assertEquals(explode(',', $data['allowed_extensions']), $attribute->getAllowedExtensions());
            }
            assertEquals($data['metric_family'], $attribute->getMetricFamily());
            assertEquals($data['default_metric_unit'], $attribute->getDefaultMetricUnit());

            if (isset($data['reference_data_name'])) {
                assertEquals($data['reference_data_name'], $attribute->getReferenceDataName());
            }
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
            $attribute = $this->getEntityOrException('Attribute', ['code' => $data['attribute']]);
            $option    = $this->getEntityOrException(
                'AttributeOption',
                ['code' => $data['code'], 'attribute' => $attribute]
            );
            $this->refresh($option);

            $option->setLocale('en_US');
            assertEquals($data['label-en_US'], (string) $option);
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
        $this->getEntityManager()->clear();
        foreach ($table->getHash() as $data) {
            $group = $this->getProductGroup($data['code']);
            $this->refresh($group);

            assertEquals($data['label-en_US'], $group->getTranslation('en_US')->getLabel());
            assertEquals($data['label-fr_FR'], $group->getTranslation('fr_FR')->getLabel());
            assertEquals($data['type'], $group->getType()->getCode());

            if ($group->getType()->isVariant()) {
                $attributes = [];
                foreach ($group->getAxisAttributes() as $attribute) {
                    $attributes[] = $attribute->getCode();
                }
                asort($attributes);
                $attributes = implode(',', $attributes);
                assertEquals($data['axis'], $attributes);
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
        $locale     = $this->getLocale($localeCode);
        $channel->addLocale($locale);
        $this->validate($channel);
        $this->persist($channel);
        $this->persist($locale);
    }

    /**
     * @param string $locale
     * @param string $channel
     *
     * @Given /^I set the "([^"]*)" locales? to the "([^"]*)" channel$/
     */
    public function iSetTheLocaleToTheChannel($locale, $channel)
    {
        return [
            new Step\Given("I am on the \"$channel\" channel page"),
            new Step\Given("I fill in \"Locales\" with \"$locale\" on the current page"),
            new Step\Given("I press \"Save\""),
        ];
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

            $this->validate($jobInstance);
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
        $jobInstance   = $this->getJobInstance($code);
        $configuration = $jobInstance->getRawConfiguration();

        foreach ($table->getRowsHash() as $property => $value) {
            $value = $this->replacePlaceholders($value);
            if (in_array($value, ['yes', 'no'])) {
                $value = 'yes' === $value;
            }

            $configuration[$property] = $value;
        }

        $jobInstance->setRawConfiguration($configuration);
        // TODO use a Saver
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
            $code  = $data['code'];
            $label = $data['label'];
            $type  = $data['type'];

            $attributes = (!isset($data['axis']) || $data['axis'] == '')
                ? [] : explode(', ', $data['axis']);

            $products = (isset($data['products'])) ? explode(', ', $data['products']) : [];

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
            $code  = $data['code'];
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
            $code      = $data['code'];
            $label     = isset($data['label']) ? $data['label'] : null;
            $isVariant = isset($data['variant']) ? $data['variant'] : 0;

            $this->createGroupType($code, $label, $isVariant);
        }
    }

    /**
     * @param string $attribute
     * @param string $rawOptions
     *
     * @Given /^the following "([^"]*)" attribute options?: (.*)$/
     */
    public function theFollowingAttributeOptions($attribute, $rawOptions)
    {
        $attribute = $this->getAttribute(strtolower($attribute));
        $options = [];
        foreach ($this->listToArray($rawOptions) as $option) {
            $option = $this->createOption($option);
            $attribute->addOption($option);
            $this->validate($option);
            $options[] = $option;
        }
        $this->getAttributeOptionSaver()->saveAll($options);
    }

    /**
     * @param string $attribute
     * @param string $referenceData
     *
     * @Given /^the following "([^"]*)" attribute reference data: (.*)$/
     */
    public function theFollowingAttributeReferenceData($attribute, $referenceData)
    {
        $attribute         = $this->getAttribute(strtolower($attribute));
        $referenceDataType = $attribute->getReferenceDataName();

        foreach ($this->listToArray($referenceData) as $code) {
            $this->createReferenceData($referenceDataType, $code, $code);
        }
        // TODO use a Saver
        $this->getEntityManager()->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following reference data?:$/
     */
    public function theFollowingReferenceData(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            if (!array_key_exists('label', $row)) {
                $row['label'] = null;
            }

            $this->createReferenceData(trim($row['type']), trim($row['code']), trim($row['label']));
        }
        // TODO use a Saver
        $this->getEntityManager()->flush();
    }

    /**
     * @param string $attribute
     * @param string $identifier
     * @param string $value
     *
     * @Given /^attribute (\w+) of "([^"]*)" should be "([^"]*)"$/
     */
    public function theOfShouldBe($attribute, $identifier, $value)
    {
        $this->clearUOW();
        $productValue = $this->getProductValue($identifier, strtolower($attribute));
        $this->assertDataEquals($productValue->getData(), $value);
    }

    /**
     * @param mixed  $data
     * @param string $value
     */
    protected function assertDataEquals($data, $value)
    {
        switch ($value) {
            case 'true':
                assertTrue($data);
                break;

            case 'false':
                assertFalse($data);
                break;

            default:
                if ($data instanceof \DateTime) {
                    $data = $data->format('Y-m-d');
                }
                assertEquals($value, $data);
        }
    }

    /**
     * @param string $lang
     * @param string $attribute
     * @param string $identifier
     * @param string $value
     *
     * @Given /^the (\w+) (\w+) of "([^"]*)" should be "([^"]*)"$/
     */
    public function theLocalizableOfShouldBe($lang, $attribute, $identifier, $value)
    {
        $this->clearUOW();
        $productValue = $this->getProductValue($identifier, strtolower($attribute), $this->locales[$lang]);

        $this->assertDataEquals($productValue->getData(), $value);
    }

    /**
     * We use the tag |NL| for \n in gherkin
     *
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
        $value = str_replace('|NL|', "\n", $value);
        $this->clearUOW();
        $productValue = $this->getProductValue($identifier, strtolower($attribute), $this->locales[$lang], $scope);

        $this->assertDataEquals($productValue->getData(), $value);
    }

    /**
     * @param string    $attribute
     * @param string    $products
     * @param TableNode $table
     *
     * @Given /^the prices "([^"]*)" of products? (.*) should be:$/
     */
    public function thePricesOfProductsShouldBe($attribute, $products, TableNode $table)
    {
        $this->clearUOW();
        foreach ($this->listToArray($products) as $identifier) {
            $productValue = $this->getProductValue($identifier, strtolower($attribute));

            foreach ($table->getHash() as $price) {
                $productPrice = $productValue->getPrice($price['currency']);
                if ('' === trim($price['amount'])) {
                    assertEquals(null, $productPrice ? $productPrice->getData() : $productPrice);
                } else {
                    assertEquals($price['amount'], $productPrice->getData());
                }
            }
        }
    }

    /**
     * @param string $attribute
     * @param string $products
     * @param string $optionCode
     *
     * @Given /^the option "([^"]*)" of products? (.*) should be "([^"]*)"$/
     */
    public function theOptionOfProductsShouldBe($attribute, $products, $optionCode)
    {
        $this->clearUOW();
        foreach ($this->listToArray($products) as $identifier) {
            $value      = $this->getProductValue($identifier, strtolower($attribute));
            $actualCode = $value->getOption() ? $value->getOption()->getCode() : null;
            assertEquals($optionCode, $actualCode);
        }
    }

    /**
     * @param string    $attribute
     * @param string    $products
     * @param TableNode $table
     *
     *
     * @Given /^the options "([^"]*)" of products? (.*) should be:$/
     */
    public function theOptionsOfProductsShouldBe($attribute, $products, TableNode $table)
    {
        $this->clearUOW();
        foreach ($this->listToArray($products) as $identifier) {
            $productValue = $this->getProductValue($identifier, strtolower($attribute));
            $options      = $productValue->getOptions();
            $optionCodes  = $options->map(
                function ($option) {
                    return $option->getCode();
                }
            );

            $values = array_map(
                function ($row) {
                    return $row['value'];
                },
                $table->getHash()
            );
            $values = array_filter($values);

            assertEquals(count($values), $options->count());
            foreach ($values as $value) {
                assertContains(
                    $value,
                    $optionCodes,
                    sprintf('"%s" does not contain "%s"', implode(', ', $optionCodes->toArray()), $value)
                );
            }
        }
    }

    /**
     * @param string $attribute
     * @param string $products
     * @param string $filename
     *
     * @Given /^the file "([^"]*)" of products? (.*) should be "([^"]*)"$/
     */
    public function theFileOfShouldBe($attribute, $products, $filename)
    {
        $this->clearUOW();
        foreach ($this->listToArray($products) as $identifier) {
            $productValue = $this->getProductValue($identifier, strtolower($attribute));
            $media        = $productValue->getMedia();
            if ('' === trim($filename)) {
                if ($media) {
                    assertNull($media->getOriginalFilename());
                }
            } else {
                assertEquals($filename, $media->getOriginalFilename());
            }
        }
    }

    /**
     * @param string $attribute
     * @param string $products
     * @param string $data
     *
     * @Given /^the metric "([^"]*)" of products? (.*) should be "([^"]*)"$/
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
     * @param string       $extension
     * @param PyStringNode $string
     *
     * @Given /^the following ([^"]*) file to import:$/
     */
    public function theFollowingFileToImport($extension, PyStringNode $string)
    {
        $extension = strtolower($extension);

        $string = $this->replacePlaceholders($string);

        $this->placeholderValues['%file to import%'] = $filename =
            sprintf(
                '%s/pim-import/behat-import-%s.%s',
                $this->placeholderValues['%tmp%'],
                substr(md5(rand()), 0, 7),
                $extension
            );
        @rmdir(dirname($filename));
        @mkdir(dirname($filename), 0777, true);

        file_put_contents($filename, (string) $string);
    }

    /**
     * @Given /^the following random files:$/
     */
    public function theFollowingRandomFiles(TableNode $table)
    {
        $directory  = realpath(__DIR__ . '/fixtures/');
        $characters = range('a', 'z');

        foreach ($table->getHash() as $row) {
            $filepath = $directory . DIRECTORY_SEPARATOR . $row['filename'];
            $content = '';
            for ($i = 0; $i < $row['size'] * 1024 * 1024; $i++) {
                $content .= $characters[rand(0, count($characters) - 1)];
            }

            touch($filepath);
            file_put_contents($filepath, $content);
        }
    }

    /**
     * @param TableNode $table
     *
     *
     * @Given /^the following CSV configuration to import:$/
     */
    public function theFollowingCSVToImport(TableNode $table)
    {
        $delimiter = ';';

        $data    = $table->getRowsHash();
        $columns = implode($delimiter, array_keys($data));

        $rows = [];
        foreach ($data as $values) {
            foreach ($values as $index => $value) {
                $value          = in_array($value, ['yes', 'no']) ? (int) $value === 'yes' : $value;
                $rows[$index][] = $value;
            }
        }
        $rows = array_map(
            function ($row) use ($delimiter) {
                return implode($delimiter, $row);
            },
            $rows
        );

        array_unshift($rows, $columns);

        return $this->theFollowingFileToImport('csv', new PyStringNode(implode("\n", $rows)));
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
     * @param int $expectedTotal
     *
     * @Then /^there should be (\d+) products?$/
     */
    public function thereShouldBeProducts($expectedTotal)
    {
        $total = count($this->getProductManager()->getProductRepository()->findAll());

        assertEquals($expectedTotal, $total);
    }

    /**
     * @param int $expectedTotal
     *
     * @Then /^there should be (\d+) attributes?$/
     */
    public function thereShouldBeAttributes($expectedTotal)
    {
        $total = count($this->getProductManager()->getAttributeRepository()->findAll());

        assertEquals($expectedTotal, $total);
    }

    /**
     * @param int $expectedTotal
     *
     * @Then /^there should be (\d+) categor(?:y|ies)$/
     */
    public function thereShouldBeCategories($expectedTotal)
    {
        $class      = $this->getContainer()->getParameter('pim_catalog.entity.category.class');
        $repository = $this->getSmartRegistry()->getRepository($class);
        $total      = count($repository->findAll());

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

        foreach ($table->getRowsHash() as $rawCode => $value) {
            $infos = $this->getFieldExtractor()->extractColumnInfo($rawCode);

            $attribute     = $infos['attribute'];
            $attributeCode = $attribute->getCode();
            $localeCode    = $infos['locale_code'];
            $scopeCode     = $infos['scope_code'];
            $priceCurrency = isset($infos['price_currency']) ? $infos['price_currency'] : null;
            $productValue  = $product->getValue($attributeCode, $localeCode, $scopeCode);

            if ('' === $value) {
                assertEmpty((string) $productValue);
            } elseif ('media' === $attribute->getBackendType()) {
                // media filename is auto generated during media handling and cannot be guessed
                // (it contains a timestamp)
                if ('**empty**' === $value) {
                    assertEmpty((string) $productValue);
                } else {
                    assertTrue(false !== strpos((string) $productValue, $value));
                }
            } elseif ('prices' === $attribute->getBackendType() && null !== $priceCurrency) {
                // $priceCurrency can be null if we want to test all the currencies at the same time
                // in this case, it's a simple string comparison
                // example: 180.00 EUR, 220.00 USD

                $price = $productValue->getPrice($priceCurrency);
                assertEquals($value, $price->getData());
            } elseif ('date' === $attribute->getBackendType()) {
                assertEquals($value, $productValue->getDate()->format('Y-m-d'));
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
     *
     * @throws \Exception
     */
    public function theFamilyOfShouldBe($productCode, $familyCode)
    {
        $family = $this->getProduct($productCode)->getFamily();
        if (!$family) {
            throw new \Exception(sprintf('Product "%s" doesn\'t have a family', $productCode));
        }
        assertEquals($familyCode, $family->getCode());
    }

    /**
     * @param string $productCode
     * @param string $categoryCodes
     *
     *
     * @Given /^(?:the )?categor(?:y|ies) of "([^"]*)" should be "([^"]*)"$/
     */
    public function theCategoriesOfShouldBe($productCode, $categoryCodes)
    {
        $product    = $this->getProduct($productCode);
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

        // TODO replace by call to a saver
        $this->flush();
    }

    /**
     * @param string $group
     * @param array  $products
     *
     * @Then /^"([^"]*)" group should contain "([^"]*)"$/
     *
     * @throws \Exception
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
     * @param string $userGroupName
     *
     * @return \Oro\Bundle\UserBundle\Entity\Group
     *
     * @Then /^there should be a "([^"]+)" user group$/
     */
    public function getUserGroup($userGroupName)
    {
        return $this->getEntityOrException('UserGroup', ['name' => $userGroupName]);
    }

    /**
     * @param string $userRoleName
     *
     * @return \Oro\Bundle\UserBundle\Entity\Role
     *
     * @Then /^there should be a "([^"]+)" user role$/
     */
    public function getUserRole($userRoleName)
    {
        return $this->getEntityOrException('Role', ['label' => $userRoleName]);
    }

    /**
     * @param string $roleLabel
     *
     * @return \Oro\Bundle\UserBundle\Entity\Role
     */
    public function getRole($roleLabel)
    {
        return $this->getEntityOrException('Role', ['label' => $roleLabel]);
    }

    /**
     * @param string $sku
     *
     * @throws \InvalidArgumentException
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     */
    public function getProduct($sku)
    {
        $product = $this->getProductRepository()->findOneByIdentifier($sku);

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
        return $this->getEntityOrException('User', ['username' => $username]);
    }

    /**
     * @param string $username
     * @param string $searchedLabel
     * @param string $associationType Can be 'group' or 'role'
     *
     * @return bool
     *
     * @Then /^the user "([^"]+)" should be in the "([^"]+)" (group)$/
     * @Then /^the user "([^"]+)" should have the "([^"]+)" (role)$/
     */
    public function checkUserAssociationExists($username, $searchedLabel = null, $associationType = null)
    {
        $user = $this->getUser($username);
        if ($searchedLabel && $associationType == 'group' && !$user->hasGroup($searchedLabel)) {
            throw new \InvalidArgumentException(
                sprintf("The user %s does not belong to the '%s' group", $username, $searchedLabel)
            );
        }
        if ($searchedLabel && $associationType == 'role' && !$user->hasRole($searchedLabel)) {
            throw new \InvalidArgumentException(
                sprintf("The user %s does not have the '%s' role", $username, $searchedLabel)
            );
        }
    }

    /**
     * @param string $username
     * @param int    $count
     * @param string $associationType Can be 'group' or 'role'
     *
     * @return bool
     *
     * @Then /^the user "([^"]+)" should be in (\d+) (group)s?$/
     * @Then /^the user "([^"]+)" should(?: still)? have (\d+) (role)s?$/
     */
    public function checkUserAssociationsCount($username, $count, $associationType)
    {
        $user = $this->getUser($username);
        $this->refresh($user);
        $actualCount = null;
        if ($associationType == 'group') {
            $actualCount = count($user->getGroupNames());
        } elseif ($associationType == 'role') {
            $actualCount = count($user->getRoles());
        }
        if ($actualCount != $count) {
            throw new \InvalidArgumentException(
                sprintf("Expected %d %s(s) for User %s, found %d", $count, $associationType, $username, $actualCount)
            );
        }
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $attribute
     *
     * @Given /^I\'ve removed the "([^"]*)" attribute$/
     */
    public function iVeRemovedTheAttribute($attribute)
    {
        // TODO use a Remover
        $this->remove($this->getAttribute($attribute));
    }

    /**
     * @param string $product
     * @param string $family
     *
     * @Given /^I set product "([^"]*)" family to "([^"]*)"$/
     */
    public function iSetProductFamilyTo($identifier, $family)
    {
        $product = $this->getProduct($identifier);
        $product->setFamily($this->getFamily($family));

        $this->validate($product);
        $this->getProductSaver()->save($product);
    }

    /**
     * Unlink all product media
     *
     * @param string $productName
     *
     * @Given /^I delete "([^"]+)" media from filesystem$/
     */
    public function iDeleteProductMediaFromFilesystem($productName)
    {
        $product      = $this->getProduct($productName);
        $allMedia     = $product->getMedia();
        $mountManager = $this->getMountManager();
        foreach ($allMedia as $media) {
            if (null !== $media) {
                $fs = $mountManager->getFilesystem($media->getStorage());
                $fs->delete($media->getKey());
            }
        }
    }

    /**
     * @param string $attribute
     * @param string $family
     * @param string $channel
     *
     * @Then /^attribute "([^"]*)" should be required in family "([^"]*)" for channel "([^"]*)"$/
     */
    public function attributeShouldBeRequiredInFamilyForChannel($attribute, $family, $channel)
    {
        $requirement = $this->getAttributeRequirement($attribute, $family, $channel);

        assertNotNull($requirement);
        assertTrue($requirement->isRequired());
    }

    /**
     * @param string $attribute
     * @param string $family
     * @param string $channel
     *
     * @Given /^attribute "([^"]*)" should be optional in family "([^"]*)" for channel "([^"]*)"$/
     */
    public function attributeShouldBeOptionalInFamilyForChannel($attribute, $family, $channel)
    {
        $requirement = $this->getAttributeRequirement($attribute, $family, $channel);

        assertNotNull($requirement);
        assertFalse($requirement->isRequired());
    }

    /**
     * @param string $identifier
     *
     * @Given /^the history of the product "([^"]*)" has been built$/
     */
    public function theHistoryOfTheProductHasBeenBuilt($identifier)
    {
        $product = $this->getProduct($identifier);
        $this->getVersionManager()->setRealTimeVersioning(true);
        $versions = $this->getVersionManager()->buildPendingVersions($product);
        foreach ($versions as $version) {
            // TODO replace by call to a saver
            $this->validate($version);
            $this->persist($version);
            $this->flush($version);
        }
    }

    /**
     * @param string $attributeCode
     * @param string $familyCode
     * @param string $channelCode
     *
     * @return AttributeRequirement|null
     */
    protected function getAttributeRequirement($attributeCode, $familyCode, $channelCode)
    {
        $em   = $this->getEntityManager();
        $repo = $em->getRepository('PimCatalogBundle:AttributeRequirement');

        $attribute = $this->getAttribute($attributeCode);
        $family    = $this->getFamily($familyCode);
        $channel   = $this->getChannel($channelCode);

        return $repo->findOneBy(
            [
                'attribute' => $attribute,
                'family'    => $family,
                'channel'   => $channel,
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
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @Given /^I set the updated date of the (product "([^"]+)") to "([^"]+)"$/
     */
    public function theProductUpdatedDateIs(ProductInterface $product, $identifier, $expected)
    {
        $product->setUpdated(new \DateTime($expected));

        $this->getProductSaver()->save($product, ['recalculate' => false]);
    }

    /**
     * Asserts that we have less than a minute interval between the product updated date and the argument
     *
     * @Then /^the (product "([^"]+)") updated date should be close to "([^"]+)"$/
     */
    public function theProductUpdatedDateShouldBeCloseTo(ProductInterface $product, $identifier, $expected)
    {
        assertLessThan(60, abs(strtotime($expected) - $product->getUpdated()->getTimestamp()));
    }

    /**
     * Asserts that we have more than a minute interval between the product updated date and the argument
     *
     * @Then /^the (product "([^"]+)") updated date should not be close to "([^"]+)"$/
     */
    public function theProductUpdatedDateShouldNotBeCloseTo(ProductInterface $product, $identifier, $expected)
    {
        assertGreaterThan(60, abs(strtotime($expected) - $product->getUpdated()->getTimestamp()));
    }

    /**
     * @Given /^the following associations for the (product "([^"]+)"):$/
     */
    public function theFollowingAssociationsForTheProduct(ProductInterface $owner, $id, TableNode $values)
    {
        $rows = $values->getHash();

        foreach ($rows as $row) {
            $association = $owner->getAssociationForTypeCode($row['type']);

            if (null === $association) {
                $associationType = $this->getContainer()
                    ->get('pim_catalog.repository.association_type')
                    ->findOneBy(['code' => $row['type']]);

                $association = new Association();
                $association->setAssociationType($associationType);
                $owner->addAssociation($association);
            }

            $association->addProduct($this->getProduct($row['product']));
        }

        $this->getProductSaver()->save($owner, ['recalculate' => false]);
    }

    /**
     * @param string $identifier
     * @param string $attribute
     * @param string $locale
     * @param string $scope
     *
     * @throws \InvalidArgumentException
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductValueInterface
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
     * @param string $code
     * @param string $label
     * @param bool   $isVariant
     *
     * @return \Pim\Bundle\CatalogBundle\Model\GroupTypeInterface
     */
    protected function createGroupType($code, $label, $isVariant)
    {
        $type = new GroupType();
        $type->setCode($code);
        $type->setVariant($isVariant);
        $type->setLocale('en_US')->setLabel($label);

        $this->validate($type);
        $this->persist($type);

        return $type;
    }

    /**
     * @param string|array $data
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AttributeInterface
     */
    protected function createAttribute($data)
    {
        if (is_string($data)) {
            $data = [
                'code'  => $data,
                'group' => 'other',
            ];
        }

        $data = array_merge(
            [
                'code'     => null,
                'label'    => null,
                'families' => null,
                'locales'  => null,
                'type'     => 'text',
                'group'    => 'other',
            ],
            $data
        );

        if (isset($data['label']) && !isset($data['label-en_US'])) {
            $data['label-en_US'] = $data['label'];
        }

        $data['code'] = $data['code'] ?: $this->camelize($data['label']);
        unset($data['label']);

        $families = $data['families'];
        unset($data['families']);

        $locales = $data['locales'];
        unset($data['locales']);

        $data['type'] = $this->getAttributeType($data['type']);

        foreach ($data as $key => $element) {
            if (in_array($element, ['yes', 'no'])) {
                $element    = $element === 'yes';
                $data[$key] = $element;
            } elseif (in_array(
                $key,
                ['available_locales', 'date_min', 'date_max', 'number_min', 'number_max']
            ) &&
                '' === $element
            ) {
                unset($data[$key]);
            }
        }

        $attribute = $this->loadFixture('attributes', $data);

        $familiesToPersist = [];
        if ($families) {
            foreach ($this->listToArray($families) as $familyCode) {
                $family = $this->getFamily($familyCode);
                $family->addAttribute($attribute);
                $familiesToPersist[] = $family;
            }
        }

        $this->validate($attribute);

        if (null !== $locales) {
            foreach ($this->listToArray($locales) as $localeCode) {
                $attribute->addAvailableLocale($this->getLocale($localeCode));
            }
        }

        $this->persist($attribute);
        foreach ($familiesToPersist as $family) {
            $this->validate($family);
            $this->persist($family);
        }

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
     * @return \Pim\Bundle\CatalogBundle\Model\CategoryInterface
     */
    protected function createTree($code)
    {
        return $this->createCategory($code);
    }

    /**
     * @param array|string $data
     *
     * @return \Pim\Bundle\CatalogBundle\Model\CategoryInterface
     */
    protected function createCategory($data)
    {
        if (is_string($data)) {
            $data = [['code' => $data]];
        }

        $category = $this->loadFixture('categories', $data);

        /*
         * When using ODM, one must persist and flush category without product
         * before adding and persisting products inside it
         */
        $products = $category->getProducts();
        $this->validate($category);
        $this->persist($category, true);
        foreach ($products as $product) {
            $product->addCategory($category);
            $this->getProductSaver()->save($product);
        }

        return $category;
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

        $this->validate($channel);
        $this->persist($channel);
    }

    /**
     * @param string $code
     * @param string $label
     * @param string $type
     * @param array  $attributes
     * @param array  $products
     */
    protected function createProductGroup($code, $label, $type, array $attributes, array $products = [])
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
        // TODO replace by call to a saver
        $this->validate($group);
        $this->persist($group);
        $this->flush($group);

        foreach ($products as $sku) {
            if (!empty($sku)) {
                $product = $this->getProduct($sku);
                $product->addGroup($group);
                $this->validate($product);
                $this->getProductSaver()->save($product);
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

        $this->validate($associationType);
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
        $this->validate($role);
        $this->persist($role);

        return $role;
    }

    /**
     * Create an attribute option entity
     *
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface
     */
    protected function createOption($code)
    {
        $option = new AttributeOption();
        $option->setCode($code);

        return $option;
    }

    /**
     * @param string $type
     * @param string $code
     * @param string $label
     *
     * @return ReferenceDataInterface
     */
    protected function createReferenceData($type, $code, $label)
    {
        switch ($type) {
            case 'color':
            case 'colors':
                $referenceData = $this->createColorReferenceData($code, $label);
                break;
            case 'fabric':
            case 'fabrics':
                $referenceData = $this->createFabricReferenceData($code, $label);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown reference data type "%s".', $type));
        }

        $this->validate($referenceData);
        $this->getEntityManager()->persist($referenceData);

        return $referenceData;
    }

    /**
     * @param string $code
     * @param string $label
     *
     * @return Color
     */
    protected function createColorReferenceData($code, $label)
    {
        $configuration = $this->getReferenceDataRegistry()->get('color');
        $class         = $configuration->getClass();

        $color = new $class();
        $color->setCode($code);
        $color->setName($label);
        $color->setHex('#' . strtolower($code));
        $color->setRed(rand(0, 100));
        $color->setGreen(rand(0, 100));
        $color->setBlue(rand(0, 100));
        $color->setHue(rand(0, 100));
        $color->setHslSaturation(rand(0, 100));
        $color->setLight(rand(0, 100));
        $color->setHsvSaturation(rand(0, 100));
        $color->setValue(rand(0, 100));

        return $color;
    }

    /**
     * @param string $code
     * @param string $label
     *
     * @return Fabric
     */
    protected function createFabricReferenceData($code, $label)
    {
        $configuration = $this->getReferenceDataRegistry()->get('fabrics');
        $class         = $configuration->getClass();

        $fabric = new $class();
        $fabric->setCode($code);
        $fabric->setName($label);

        return $fabric;
    }

    /**
     * Create a family
     *
     * @param array|string $data
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Family
     */
    protected function createFamily($data)
    {
        if (is_string($data)) {
            $data = ['code' => $data];
        }

        if (isset($data['attributes'])) {
            $data['attributes'] = str_replace(' ', '', $data['attributes']);
        }

        foreach ($data as $key => $value) {
            if (false !== strpos($key, 'requirements-')) {
                $data[$key] = str_replace(' ', '', $value);
            }
        }

        $family = $this->loadFixture('families', $data);
        $this->getFamilySaver()->save($family);

        return $family;
    }

    /**
     * Create an attribute group
     *
     * @param array|string $data
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface
     */
    protected function createAttributeGroup($data)
    {
        if (is_string($data)) {
            $data = ['code' => $data];
        }

        $attributeGroup = $this->loadFixture('attribute_groups', $data);

        $this->persist($attributeGroup);

        return $attributeGroup;
    }

    /**
     * @param array              $data
     * @param CommentInterface[] $comments
     *
     * @return \Pim\Bundle\CommentBundle\Model\CommentInterface
     */
    protected function createComment(array $data, array $comments)
    {
        $resource  = $data['resource'];
        $createdAt = \DateTime::createFromFormat('j-M-Y', $data['created_at']);

        $comment = new Comment();
        $comment->setAuthor($this->getUser($data['author']));
        $comment->setCreatedAt($createdAt);
        $comment->setRepliedAt($createdAt);
        $comment->setBody($data['message']);
        $comment->setResourceName(ClassUtils::getClass($resource));
        $comment->setResourceId($resource->getId());

        if (isset($data['parent']) && !empty($data['parent'])) {
            $parent = $comments[$data['parent']];
            $parent->setRepliedAt($createdAt);
            $comment->setParent($parent);
            $this->validate($comment);
            $this->persist($parent);
        }

        $this->persist($comment);

        return $comment;
    }

    /**
     * Create a datagrid view
     *
     * @param array $data
     *
     * @return DatagridView
     */
    protected function createDatagridView(array $data)
    {
        $columns = array_map(
            function ($column) {
                return trim($column);
            },
            explode(',', $data['columns'])
        );

        $view = new DatagridView();
        $view->setLabel($data['label']);
        $view->setDatagridAlias($data['alias']);
        $view->setFilters(urlencode($data['filters']));
        $view->setColumns($columns);
        $view->setOwner($this->getUser('Peter'));

        $this->validate($view);
        $this->persist($view);

        return $view;
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

        if ($processor instanceof ProductProcessor) {
            $processor->setEnabledComparison(false);
        }

        $entity = $processor->process($data);

        // we encountered a bunch of invalid data in behat due to old way to import them
        // could be removed once all the fixtures will use the new API (processor, updater, validator)
        $this->validate($entity);

        return $entity;
    }

    /**
     * @param mixed $object
     *
     * @throws \InvalidArgumentException
     */
    protected function validate($object)
    {
        // TODO: split UniqueVariantAxis + spec
        // TODO: rework validation constraint to forbid to add products with same options in variant group in same time
        if ($object instanceof ProductInterface) {
            $validator = $this->getContainer()->get('pim_catalog.validator.product');
        } else {
            $validator = $this->getContainer()->get('validator');
        }
        $violations = $validator->validate($object);

        if (0 < $violations->count()) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Object "%s" is not valid, cf following constraint violations "%s"',
                    ClassUtils::getClass($object),
                    implode(', ', $messages)
                )
            );
        }
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
     * @return \Doctrine\Common\Persistence\ObjectRepository
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
     * @return \Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface
     */
    protected function getProductRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.product');
    }

    /**
     * @return MountManager
     */
    protected function getMountManager()
    {
        return $this->getContainer()->get('oneup_flysystem.mount_manager');
    }

    /**
     * @return ProductBuilderInterface
     */
    protected function getProductBuilder()
    {
        return $this->getContainer()->get('pim_catalog.builder.product');
    }

    /**
     * @return ProductSaver
     */
    protected function getProductSaver()
    {
        return $this->getContainer()->get('pim_catalog.saver.product');
    }

    /**
     * @return SaverInterface
     */
    protected function getFamilySaver()
    {
        return $this->getContainer()->get('pim_catalog.saver.family');
    }

    /**
     * @return SaverInterface
     */
    protected function getAttributeOptionSaver()
    {
        return $this->getContainer()->get('pim_catalog.saver.attribute_option');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Manager\AttributeManager
     */
    protected function getAttributeManager()
    {
        return $this->getContainer()->get('pim_catalog.manager.attribute');
    }

    /**
     * @return Filesystem[]
     */
    protected function getPimFilesystems()
    {
        return [];
    }

    /**
     * @return \Pim\Bundle\VersioningBundle\Manager\VersionManager
     */
    protected function getVersionManager()
    {
        return $this->getContainer()->get('pim_versioning.manager.version');
    }

    /**
     * @return \Pim\Component\Connector\ArrayConverter\Flat\Product\AttributeColumnInfoExtractor
     */
    protected function getFieldExtractor()
    {
        return $this->getContainer()->get('pim_connector.array_converter.flat.product.attribute_column_info_extractor');
    }

    /**
     * @return \Pim\Component\ReferenceData\ConfigurationRegistryInterface
     */
    protected function getReferenceDataRegistry()
    {
        return $this->getContainer()->get('pim_reference_data.registry');
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
     * @param object $object
     * @param bool   $flush
     *
     * TODO use Savers
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
     * @param object $object
     * @param bool   $flush
     *
     * * TODO use Removers
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
     */
    protected function flush($object = null)
    {
        if (!$object) {
            $this->flushAll();

            return;
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
