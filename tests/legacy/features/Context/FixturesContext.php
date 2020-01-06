<?php

namespace Context;

use Acme\Bundle\AppBundle\Entity\Color;
use Acme\Bundle\AppBundle\Entity\Fabric;
use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Connectivity\Connection\Application\Settings\Command\CreateConnectionCommand;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Comment\Model\Comment;
use Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductCsvImport;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductModelCsvImport;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeRequirement;
use Akeneo\Pim\Structure\Component\Model\GroupType;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleCsvExport;
use Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleCsvImport;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Behat\ChainedStepsExtension\Step;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\Util\ClassUtils;
use League\Flysystem\MountManager;
use OAuth2\OAuth2;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\FixturesContext as BaseFixturesContext;

/**
 * A context for creating entities
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FixturesContext extends BaseFixturesContext
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

    protected $username;

    /**
     * @Given There is a :connectionCode connection
     */
    public function thereIsAConnection($connectionCode)
    {
        $createConnectionCommand = new CreateConnectionCommand($connectionCode, $connectionCode, FlowType::DATA_SOURCE);
        $this->getContainer()->get('akeneo_connectivity.connection.application.handler.create_connection')->handle($createConnectionCommand);
    }

    /**
     * @param array|string $data
     *
     * @return \Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface
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

        $nonEmptyData = [];

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (!(null === $data || (is_string($data) && trim($data) === '') || (is_array($data) && empty($data)))) {
                    $nonEmptyData[$key] = $value;
                }
            }
        }

        $data = $nonEmptyData;

        if (isset($data['parent'])) {
            if (empty($data['parent'])) {
                unset($data['parent']);
            }
        }

        foreach ($data as $key => $value) {
            $data[$key] = $this->replacePlaceholders($value);
        }

        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.product');
        $processor = $this->getContainer()->get('pim_connector.processor.denormalization.product');

        $jobExecution = new JobExecution();
        $provider = new ProductCsvImport(new SimpleCsvExport([]), []);
        $params = $provider->getDefaultValues();
        $params['enabledComparison'] = false;
        $params['dateFormat'] = LocalizerInterface::DEFAULT_DATE_FORMAT;
        $params['decimalSeparator'] = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;
        $jobParameters = new JobParameters($params);
        $jobExecution->setJobParameters($jobParameters);
        $stepExecution = new StepExecution('processor', $jobExecution);
        $processor->setStepExecution($stepExecution);

        $convertedData = $converter->convert($data);
        $product = $processor->process($convertedData);
        $this->validate($product);
        $this->getProductSaver()->save($product);

        // reset the unique value set to allow to update product values
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->getContainer()->get('pim_catalog.validator.unique_axes_combination_set')->reset();

        $this->buildProductHistory($product);

        $this->refreshEsIndexes();

        return $product;
    }

    /**
     * @param array|string $data
     *
     * @return ProductInterface
     *
     * @Given /^a "([^"]*)" user/
     */
    public function createUser($data)
    {
        if (is_string($data)) {
            $data = ['username' => $data];
        } elseif (isset($data['enabled']) && in_array($data['enabled'], ['yes', 'no'])) {
            $data['enabled'] = ($data['enabled'] === 'yes');
        }

        if (!isset($data['user_default_locale'])) {
            $data['user_default_locale'] = 'en_US';
        }

        if (!isset($data['catalog_default_locale'])) {
            $data['catalog_default_locale'] = 'en_US';
        }

        foreach ($data as $key => $value) {
            $data[$key] = $this->replacePlaceholders($value);
        }

        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.user');
        $processor = $this->getContainer()->get('pim_connector.processor.denormalization.user');

        $jobExecution = new JobExecution();
        $provider = new SimpleCsvImport([]);
        $params = $provider->getDefaultValues();
        $params['enabledComparison'] = false;
        $params['dateFormat'] = LocalizerInterface::DEFAULT_DATE_FORMAT;
        $params['decimalSeparator'] = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;
        $jobParameters = new JobParameters($params);
        $jobExecution->setJobParameters($jobParameters);
        $stepExecution = new StepExecution('processor', $jobExecution);
        $processor->setStepExecution($stepExecution);

        $convertedData = $converter->convert($data);
        $user = $processor->process($convertedData);
        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }


    /**
     * @param int $numberOfProducts
     *
     * @Given /^([0-9]+) empty products(?: for the "([^"]+)" family)?$/
     */
    public function createEmptyProducts(int $numberOfProducts, string $familyCode = '')
    {
        for (;$numberOfProducts > 0; $numberOfProducts--) {
            $this->createProduct(['sku' => sprintf('product_%s', $numberOfProducts), 'family' => $familyCode]);
        }
    }

    /**
     * @param int $numberOfFamilies
     *
     * @Given /^([0-9]+) empty families$/
     */
    public function createEmptyFamilies(int $numberOfFamilies)
    {
        $families = [];

        for (; $numberOfFamilies > 0; $numberOfFamilies--) {
            $family = $this->getService('pim_catalog.factory.family')->create();
            $family->setCode(sprintf('family_%s', $numberOfFamilies));
            $this->validate($family);
            $families[] = $family;
        }

        $this->getFamilySaver()->saveAll($families);
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
     * @Given the :identifier product created at :createdAt
     */
    public function theProductCreatedAt(string $identifier, string $createdAt)
    {
        $product = $this->createProduct(['sku' => $identifier]);

        $this->getContainer()->get('doctrine')->getConnection()->update(
            'pim_catalog_product',
            ['created' => $createdAt],
            ['id' => $product->getId()]
        );

        $this->refresh($product);
        $this->getProductSaver()->save($product);
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following users?:$/
     */
    public function theFollowingUser(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->createUser($data);
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
     * @return ProductInterface
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
        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.family');
        $processor = $this->getContainer()->get('pim_connector.processor.denormalization.family');
        $saver     = $this->getContainer()->get('pim_catalog.saver.family');

        $families = [];
        foreach ($table->getHash() as $data) {
            $families[] = $processor->process($converter->convert($data));
        }

        $saver->saveAll($families);
    }

    /**
     * @Given the family :familyCode has the attributes :attributeCodes
     */
    public function theFamilyHasTheAttributes($familyCode, $attributeCodes)
    {
        $familyRepository = $this->getContainer()->get('pim_catalog.repository.family');
        $familySaver = $this->getContainer()->get('pim_catalog.saver.family');
        $attributeRepository = $this->getContainer()->get('pim_catalog.repository.attribute');

        $family = $familyRepository->findOneByIdentifier($familyCode);

        if (null === $family) {
            throw new \Exception(sprintf('The family "%s" does not exist.', $familyCode));
        }

        foreach ($this->listToArray($attributeCodes) as $attributeCode) {
            $attribute = $attributeRepository->findOneByIdentifier($attributeCode);

            if (null === $family) {
                throw new \Exception(sprintf('The attribute "%s" does not exist.', $attributeCode));
            }

            $family->addAttribute($attribute);
        }

        $familySaver->save($family);
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following family variants?:$/
     */
    public function theFollowingFamilyVariants(TableNode $table)
    {
        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.family_variant');
        $processor = $this->getContainer()->get('pim_connector.processor.denormalization.family_variant');
        $saver = $this->getContainer()->get('pim_catalog.saver.family_variant');

        foreach ($table->getHash() as $data) {
            $saver->save($processor->process($converter->convert($data)));
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following root product models?:$/
     */
    public function theFollowingRootProductModels(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replacePlaceholders($value);
            }

            $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.product_model');
            $processor = $this->getContainer()->get('pim_connector.processor.denormalization.root_product_model');

            $jobExecution = new JobExecution();
            $provider = new ProductModelCsvImport(new SimpleCsvExport([]), []);
            $params = $provider->getDefaultValues();
            $params['enabledComparison'] = false;
            $params['dateFormat'] = LocalizerInterface::DEFAULT_DATE_FORMAT;
            $params['decimalSeparator'] = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;
            $jobParameters = new JobParameters($params);
            $jobExecution->setJobParameters($jobParameters);
            $stepExecution = new StepExecution('processor', $jobExecution);
            $processor->setStepExecution($stepExecution);

            $convertedData = $converter->convert($data);
            $productModel = $processor->process($convertedData);

            $errors = $this->getContainer()->get('pim_catalog.validator.product_model')->validate($productModel);
            if (0 !== $errors->count()) {
                throw new \LogicException('Product model could not be updated, invalid data provided.');
            }

            $this->getContainer()->get('pim_catalog.saver.product_model')->save($productModel);

            $uniqueAxesCombinationSet = $this->getContainer()->get('pim_catalog.validator.unique_axes_combination_set');
            $uniqueAxesCombinationSet->reset();

            $this->refresh($productModel);
            $this->refreshEsIndexes();
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following sub product models?:$/
     */
    public function theFollowingSubProductModels(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replacePlaceholders($value);
            }

            $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.product_model');
            $processor = $this->getContainer()->get('pim_connector.processor.denormalization.sub_product_model');

            $jobExecution = new JobExecution();
            $provider = new ProductModelCsvImport(new SimpleCsvExport([]), []);
            $params = $provider->getDefaultValues();
            $params['enabledComparison'] = false;
            $params['dateFormat'] = LocalizerInterface::DEFAULT_DATE_FORMAT;
            $params['decimalSeparator'] = LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR;
            $jobParameters = new JobParameters($params);
            $jobExecution->setJobParameters($jobParameters);
            $stepExecution = new StepExecution('processor', $jobExecution);
            $processor->setStepExecution($stepExecution);

            $convertedData = $converter->convert($data);
            $productModel = $processor->process($convertedData);

            $errors = $this->getContainer()->get('pim_catalog.validator.product_model')->validate($productModel);
            if (0 !== $errors->count()) {
                throw new \LogicException('Product model could not be updated, invalid data provided.');
            }

            $this->getContainer()->get('pim_catalog.saver.product_model')->save($productModel);

            $uniqueAxesCombinationSet = $this->getContainer()->get('pim_catalog.validator.unique_axes_combination_set');
            $uniqueAxesCombinationSet->reset();

            $this->refresh($productModel);
            $this->refreshEsIndexes();
        }
    }

    /**
     * Generates a given number of families.
     *
     * @param int $familyNumber
     *
     * @Given /^([0-9]+) generated families$/
     */
    public function generatedFamilies($familyNumber)
    {
        $table = [['code']];
        for ($i = 1; $i <= $familyNumber; $i++) {
            $familyCode = sprintf('family_%d', $i);
            $table[] = [$familyCode];
        }

        $tableNode = new TableNode($table);

        return $this->theFollowingFamilies($tableNode);
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following attribute groups?:$/
     */
    public function theFollowingAttributeGroups(TableNode $table)
    {
        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.attribute_group');
        $processor = $this->getContainer()->get('pim_connector.processor.denormalization.attribute_group');
        $saver     = $this->getContainer()->get('pim_catalog.saver.attribute_group');

        foreach ($table->getHash() as $data) {
            $saver->save($processor->process($converter->convert($data)));
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following attributes?:$/
     */
    public function theFollowingAttributes(TableNode $table)
    {
        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.attribute');
        $processor = $this->getContainer()->get('pim_connector.processor.denormalization.attribute');
        $saver     = $this->getContainer()->get('pim_catalog.saver.attribute');

        foreach ($table->getHash() as $data) {
            $attribute = $processor->process($converter->convert($data));

            if (isset($data['unique'])) {
                // Due to Pim/Component/Catalog/Updater/AttributeUpdater.php:226 (SDS-998)
                $attribute->setUnique($data['unique'] === '1');
            }

            $this->validate($attribute);
            $saver->save($attribute);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following attribute label translations:$/
     */
    public function theFollowingAttributeLabelTranslations(TableNode $table)
    {
        $this->getEntityManager()->clear();

        foreach ($table->getHash() as $data) {
            $attribute = $this->getAttribute($data['attribute']);
            $standardData = [
                'labels' => [
                    $this->getLocaleCode($data['locale']) => $data['label']
                ]
            ];
            $updater = $this->getContainer()->get('pim_catalog.updater.attribute');
            $updater->update($attribute, $standardData);
            $this->validate($attribute);
            $this->getContainer()->get('pim_catalog.saver.attribute')->save($attribute);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following product values?:$/
     */
    public function theFollowingProductValues(TableNode $table)
    {
        $products = [];
        foreach ($table->getHash() as $row) {
            $row = array_merge(['locale' => null, 'scope' => null, 'value' => null], $row);

            $attributeCode = $row['attribute'];
            if ($row['locale']) {
                $attributeCode .= '-' . $row['locale'];
            }
            if ($row['scope']) {
                $attributeCode .= '-' . $row['scope'];
            }

            $products[$row['product']][$attributeCode] = $this->replacePlaceholders($row['value']);
        }

        foreach ($products as $identifier => $product) {
            $product['sku'] = $identifier;

            $this->createProduct($product);
        }
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

        $attributeBulks = [];
        $attributeConverter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.attribute');
        $attributeProcessor = $this->getContainer()->get('pim_connector.processor.denormalization.attribute');
        foreach ($attributeData as $index => $data) {
            $convertedData = $attributeConverter->convert($data);
            $attribute = $attributeProcessor->process($convertedData);
            $this->validate($attribute);
            $attributeBulks[$index % 200][]= $attribute;
        }
        foreach ($attributeBulks as $attributes) {
            $this->getContainer()->get('pim_catalog.saver.attribute')->saveAll($attributes);
        }

        $optionsBulks = [];
        $optionConverter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.attribute_option');
        $optionProcessor = $this->getContainer()->get('pim_connector.processor.denormalization.attribute_option');
        foreach ($optionData as $index => $data) {
            $convertedData = $optionConverter->convert($data);
            $option = $optionProcessor->process($convertedData);
            $this->validate($option);
            $optionsBulks[$index % 200][]= $option;
        }
        foreach ($optionsBulks as $options) {
            $this->getContainer()->get('pim_catalog.saver.attribute_option')->saveAll($options);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following famil(?:y|ies):$/
     */
    public function thereShouldBeTheFollowingFamilies(TableNode $table)
    {
        $this->getEntityManager()->clear();
        foreach ($table->getHash() as $data) {
            $family = $this->getFamily($data['code']);
            unset($data['code']);

            foreach ($data as $key => $value) {
                $matches = null;
                if ('attributes' === $key) {
                    $this->assertArrayEquals(explode(',', $value), $family->getAttributeCodes());
                } elseif ('attribute_as_label' === $key) {
                    Assert::assertEquals($value, $family->getAttributeAsLabel()->getCode());
                } elseif ('attribute_as_image' === $key) {
                    if ('' === $value) {
                        Assert::assertNull($family->getAttributeAsImage());
                    } else {
                        Assert::assertEquals($value, $family->getAttributeAsImage()->getCode());
                    }
                } elseif (preg_match('/^label-(?P<locale>.*)$/', $key, $matches)) {
                    Assert::assertEquals($value, $family->getTranslation($matches['locale'])->getLabel());
                } elseif (preg_match('/^requirements-(?P<channel>.*)$/', $key, $matches)) {
                    $requirements = [];
                    foreach ($family->getAttributeRequirements() as $requirement) {
                        if ($matches['channel'] === $requirement->getChannel()->getCode()) {
                            $requirements[] = $requirement->getAttribute()->getCode();
                        }
                    }
                    $this->assertArrayEquals(explode(',', $value), $requirements);
                } else {
                    throw new \InvalidArgumentException(sprintf('Cannot check "%s" attribute of the family', $key));
                }
            }
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following family variants:$/
     */
    public function thereShouldBeTheFollowingFamilyVariants(TableNode $table)
    {
        $this->getEntityManager()->clear();
        foreach ($table->getHash() as $data) {
            $familyVariant = $this->getFamilyVariant($data['code']);
            unset($data['code']);

            foreach ($data as $key => $value) {
                $matches = null;
                if ('family' === $key) {
                    Assert::assertEquals($value, $familyVariant->getFamily()->getCode());
                } elseif (preg_match('/^label-(?P<locale>.*)$/', $key, $matches)) {
                    Assert::assertEquals($value, $familyVariant->getTranslation($matches['locale'])->getLabel());
                } elseif (preg_match('/^variant-attributes_(?P<level>.*)$/', $key, $matches)) {
                    $variantAttributeSet = $familyVariant->getVariantAttributeSet($matches['level']);

                    if (null === $variantAttributeSet) {
                        Assert::assertEmpty($value);
                    } else {
                        $variantAttributeCodes = $variantAttributeSet->getAttributes()->map(
                            function (AttributeInterface $attribute) {
                                return $attribute->getCode();
                            }
                        )->toArray();

                        $this->assertArrayEquals(explode(',', $value), $variantAttributeCodes);
                    }
                } elseif (preg_match('/^variant-axes_(?P<level>.*)$/', $key, $matches)) {
                    $variantAttributeSet = $familyVariant->getVariantAttributeSet($matches['level']);

                    if (null === $variantAttributeSet) {
                        Assert::assertEmpty($value);
                    } else {
                        $variantAxeCodes= $variantAttributeSet->getAxes()->map(
                            function (AttributeInterface $attribute) {
                                return $attribute->getCode();
                            }
                        )->toArray();

                        $this->assertArrayEquals(explode(',', $value), $variantAxeCodes);
                    }
                } else {
                    throw new \InvalidArgumentException(sprintf('Cannot check "%s" attribute of the family', $key));
                }
            }
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following currencies:$/
     */
    public function thereShouldBeTheFollowingCurrencies(TableNode $table)
    {
        $this->getEntityManager()->clear();
        foreach ($table->getHash() as $data) {
            $currency = $this->getCurrency($data['code']);

            Assert::assertEquals($data['activated'], (int) $currency->isActivated());
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following locales:$/
     */
    public function thereShouldBeTheFollowingLocales(TableNode $table)
    {
        $this->getEntityManager()->clear();
        foreach ($table->getHash() as $data) {
            $locale = $this->getLocale($data['code']);

            Assert::assertNotNull($locale);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following channels:$/
     */
    public function thereShouldBeTheFollowingChannels(TableNode $table)
    {
        $this->getEntityManager()->clear();
        foreach ($table->getHash() as $data) {
            $channel = $this->getChannel($data['code']);
            unset($data['code']);

            foreach ($data as $key => $value) {
                $matches = null;
                if ('tree' === $key) {
                    Assert::assertEquals($value, $channel->getCategory()->getCode());
                } elseif (preg_match('/^label-(?P<locale>.*)$/', $key, $matches)) {
                    Assert::assertEquals($value, $channel->getTranslation($matches['locale'])->getLabel());
                } elseif ('locales' === $key) {
                    $this->assertArrayEquals(explode(',', $value), $channel->getLocaleCodes());
                } elseif ('currencies' === $key) {
                    $currencyCodes = [];
                    foreach ($channel->getCurrencies() as $currency) {
                        $currencyCodes[] = $currency->getCode();
                    }
                    $this->assertArrayEquals(explode(',', $value), $currencyCodes);
                } elseif ('conversion_units' === $key) {
                    $formattedUnits = [];
                    foreach ($channel->getConversionUnits() as $attribute => $measure) {
                        $formattedUnits[] = sprintf("%s:%s", $attribute, $measure);
                    }
                    $this->assertArrayEquals(explode(',', $value), $formattedUnits);
                } else {
                    throw new \InvalidArgumentException(sprintf('Cannot check "%s" attribute of the channel', $key));
                }
            }
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following group types:$/
     */
    public function thereShouldBeTheFollowingGroupTypes(TableNode $table)
    {
        $this->getEntityManager()->clear();
        foreach ($table->getHash() as $data) {
            $groupType = $this->getGroupType($data['code']);
            unset($data['code']);

            foreach ($data as $key => $value) {
                $matches = null;
                if (preg_match('/^label-(?P<locale>.*)$/', $key, $matches)) {
                    Assert::assertEquals($value, $groupType->getTranslation($matches['locale'])->getLabel());
                } else {
                    throw new \InvalidArgumentException(
                        sprintf('Cannot check "%s" attribute of the group type', $key)
                    );
                }
            }
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following attribute groups:$/
     */
    public function thereShouldBeTheFollowingAttributeGroups(TableNode $table)
    {
        $this->getEntityManager()->clear();
        foreach ($table->getHash() as $data) {
            $group = $this->getAttributeGroup($data['code']);

            Assert::assertEquals($data['label-en_US'], $group->getTranslation('en_US')->getLabel());
            Assert::assertEquals($data['sort_order'], $group->getSortOrder());

            $attributes = $group->getAttributes();
            $codes = [];
            foreach ($attributes as $attribute) {
                $codes[] = $attribute->getCode();
            }
            asort($codes);
            Assert::assertEquals($data['attributes'], implode(',', $codes));
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following options:$/
     */
    public function thereShouldBeTheFollowingOptions(TableNode $table)
    {
        $this->getEntityManager()->clear();
        foreach ($table->getHash() as $data) {
            $attribute = $this->getEntityOrException('Attribute', ['code' => $data['attribute']]);
            $option    = $this->getEntityOrException(
                'AttributeOption',
                ['code' => $data['code'], 'attribute' => $attribute]
            );
            $option->setLocale('en_US');
            Assert::assertEquals($data['label-en_US'], (string) $option);

            if (isset($data['sort_order'])) {
                Assert::assertEquals($data['sort_order'], (string) $option->getSortOrder());
            }
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^there should be the following categories:$/
     */
    public function thereShouldBeTheFollowingCategories(TableNode $table)
    {
        $this->getEntityManager()->clear();
        foreach ($table->getHash() as $data) {
            $category = $this->getCategory($data['code']);
            Assert::assertEquals($data['label'], $category->getTranslation('en_US')->getLabel());
            if (empty($data['parent'])) {
                Assert::assertNull($category->getParent());
            } else {
                Assert::assertEquals($data['parent'], $category->getParent()->getCode());
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
        $this->getEntityManager()->clear();
        foreach ($table->getHash() as $data) {
            $associationType = $this->getAssociationType($data['code']);
            unset($data['code']);

            foreach ($data as $key => $value) {
                $matches = null;
                if (preg_match('/^label-(?P<locale>.*)$/', $key, $matches)) {
                    Assert::assertEquals($value, $associationType->getTranslation($matches['locale'])->getLabel());
                } else {
                    throw new \InvalidArgumentException(
                        sprintf('Cannot check "%s" attribute of the association type', $key)
                    );
                }
            }
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

            Assert::assertEquals($data['label-en_US'], $group->getTranslation('en_US')->getLabel());
            Assert::assertEquals($data['label-fr_FR'], $group->getTranslation('fr_FR')->getLabel());
            Assert::assertEquals($data['type'], $group->getType()->getCode());
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following channels?:$/
     */
    public function theFollowingChannels(TableNode $table)
    {
        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.channel');
        $processor = $this->getContainer()->get('pim_connector.processor.denormalization.channel');
        $saver     = $this->getContainer()->get('pim_catalog.saver.channel');

        foreach ($table->getHash() as $data) {
            $saver->save($processor->process($converter->convert($data)));
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
        $this->getContainer()->get('pim_catalog.saver.channel')->save($channel);
        $this->getContainer()->get('pim_catalog.saver.locale')->save($locale);
    }

    /**
     * @param string $locale
     * @param string $channel
     *
     * @return Step\SubstepInterface[]
     *
     * @Given /^I set the "([^"]*)" locales? to the "([^"]*)" channel$/
     */
    public function iSetTheLocaleToTheChannel($locale, $channel)
    {
        return [
            new Step\Given("I am on the \"$channel\" channel page"),
            new Step\Given("I fill in \"Locales\" with \"$locale\" on the current page"),
            new Step\Given("I press \"Save\""),
            new Step\Given("I should not see the text \"There are unsaved changes.\"")
        ];
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following jobs?:$/
     */
    public function theFollowingJobs(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $jobInstance = new JobInstance($data['connector'], $data['type'], $data['alias']);
            $jobInstance->setCode($data['code']);
            $jobInstance->setLabel($data['label']);
            $this->getContainer()->get('akeneo_batch.saver.job_instance')->save($jobInstance);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following product groups?:$/
     */
    public function theFollowingProductGroups(TableNode $table)
    {
        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.group');
        $processor = $this->getContainer()->get('pim_connector.processor.denormalization.group');
        $saver     = $this->getContainer()->get('pim_catalog.saver.group');

        foreach ($table->getHash() as $data) {
            $saver->save($processor->process($converter->convert($data)));
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following association types?:$/
     */
    public function theFollowingAssociationTypes(TableNode $table)
    {
        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.association_type');
        $processor = $this->getContainer()->get('pim_connector.processor.denormalization.association_type');
        $saver     = $this->getContainer()->get('pim_catalog.saver.association_type');

        foreach ($table->getHash() as $data) {
            $saver->save($processor->process($converter->convert($data)));
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following group types?:$/
     */
    public function theFollowingGroupTypes(TableNode $table)
    {
        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.group_type');
        $processor = $this->getContainer()->get('pim_connector.processor.denormalization.group_type');
        $saver     = $this->getContainer()->get('pim_catalog.saver.group_type');

        foreach ($table->getHash() as $data) {
            $saver->save($processor->process($converter->convert($data)));
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
    }

    /**
     * @param string $attribute
     * @param string $identifier
     * @param string $value
     *
     * @Given /^attribute (\w+) of "([^"]*)" should be "(.*)"$/
     * @Given /^the product value (\w+) of "([^"]*)" should be "(.*)"$/
     */
    public function theOfShouldBe($attribute, $identifier, $value)
    {
        $this->getMainContext()->getSubcontext('hook')->clearUOW();
        $productValue = $this->getProductValue($identifier, strtolower($attribute));

        $this->assertProductDataValueEquals($value, $productValue, strtolower($attribute));
    }

    /**
     * @param string $lang
     * @param string $attribute
     * @param string $identifier
     * @param string $value
     *
     * @Given /^the (\w+) localizable value (\w+) of "([^"]*)" should be "(.*)"$/
     */
    public function theLocalizableValueOfShouldBe($lang, $attribute, $identifier, $value)
    {
        $this->getMainContext()->getSubcontext('hook')->clearUOW();
        $productValue = $this->getProductValue($identifier, strtolower($attribute), $this->locales[$lang]);

        $this->assertProductDataValueEquals($value, $productValue, strtolower($attribute));
    }

    /**
     * @param string $channel
     * @param string $attribute
     * @param string $identifier
     * @param string $value
     *
     * @Given /^the (\w+) scopable value (\w+) of "([^"]*)" should be "(.*)"$/
     */
    public function theScopableValueOfShouldBe($channel, $attribute, $identifier, $value)
    {
        $this->getMainContext()->getSubcontext('hook')->clearUOW();
        $productValue = $this->getProductValue($identifier, strtolower($attribute), null, $channel);

        $this->assertProductDataValueEquals($value, $productValue, strtolower($attribute));
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
     * @Given /^the ((?!product)\w+) (\w+) (\w+) of "([^"]*)" should be "(.*)"$/
     */
    public function theScopableAndLocalizableOfShouldBe($lang, $scope, $attribute, $identifier, $value)
    {
        $locale = 'unlocalized' === $lang ? null : $this->locales[$lang];
        $value = str_replace('|NL|', "\n", $value);
        $this->getMainContext()->getSubcontext('hook')->clearUOW();
        $productValue = $this->getProductValue($identifier, strtolower($attribute), $locale, $scope);

        $this->assertProductDataValueEquals($value, $productValue, strtolower($attribute));
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
        $this->getMainContext()->getSubcontext('hook')->clearUOW();

        foreach ($this->listToArray($products) as $identifier) {
            $productValue = $this->getProductValue($identifier, strtolower($attribute));
            foreach ($table->getHash() as $price) {
                $infos = ['price_currency' => $price['currency']];
                $this->assertProductDataValueEquals(
                    ('' === trim($price['amount'])) ? null : $price['amount'],
                    $productValue,
                    strtolower($attribute),
                    $infos
                );
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
        $this->getMainContext()->getSubcontext('hook')->clearUOW();
        foreach ($this->listToArray($products) as $identifier) {
            $value      = $this->getProductValue($identifier, strtolower($attribute));
            $actualCode = $value->getData();
            Assert::assertEquals($optionCode, $actualCode);
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
        $this->getMainContext()->getSubcontext('hook')->clearUOW();
        foreach ($this->listToArray($products) as $identifier) {
            $productValue = $this->getProductValue($identifier, strtolower($attribute));
            $optionCodes  = $productValue->getData();

            $values = array_map(
                function ($row) {
                    return $row['value'];
                },
                $table->getHash()
            );
            $values = array_filter($values);

            Assert::assertEquals(count($values), count($optionCodes));
            foreach ($values as $value) {
                Assert::assertContains(
                    $value,
                    $optionCodes,
                    sprintf('"%s" does not contain "%s"', implode(', ', $optionCodes), $value)
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
        $this->getMainContext()->getSubcontext('hook')->clearUOW();
        $this->spin(function () use ($attribute, $products, $filename) {
            foreach ($this->listToArray($products) as $identifier) {
                $productValue = $this->getProductValue($identifier, strtolower($attribute));
                $media        = $productValue->getData();
                if ('' === trim($filename)) {
                    if ($media) {
                        Assert::assertNull($media->getOriginalFilename());
                    }
                } else {
                    Assert::assertEquals($filename, $media->getOriginalFilename());
                }
            }
            return true;
        }, sprintf(
            'Cannot assert that the value for the attribute "%s" is "%s" for the products "%s"',
            $attribute,
            $filename,
            implode(',', $this->listToArray($products))
        ));
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
        $this->getMainContext()->getSubcontext('hook')->clearUOW();

        foreach ($this->listToArray($products) as $identifier) {
            $productValue = $this->getProductValue($identifier, strtolower($attribute));
            $this->assertProductDataValueEquals($data, $productValue, strtolower($attribute));
        }
    }

    /**
     * @param string $attributeCode
     * @param string $products
     *
     * @Given /^the value "([^"]*)" of products? (.*) should be empty$/
     */
    public function theValueOfProductsShouldBeEmpty($attributeCode, $products)
    {
        foreach ($this->listToArray($products) as $identifier) {
            $product = $this->getProduct($identifier);
            Assert::assertNull($product->getValue(strtolower($attributeCode)));
        }
    }

    /**
     * @param string $attribute
     * @param string $identifier
     * @param string $value
     *
     * @Given /^the product model value (\w+) of "([^"]*)" should be "(.*)"$/
     */
    public function theProductModelValueOfShouldBe(string $attribute, string $identifier, string $value)
    {
        $productValue = $this->getProductModelValue($identifier, strtolower($attribute));

        $this->assertProductDataValueEquals($value, $productValue, strtolower($attribute));
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
     * @param string    $code
     * @param TableNode $table
     *
     * @Given /^import directory of "([^"]*)" contains the following media:$/
     */
    public function importDirectoryOfContainsTheFollowingMedia($code, TableNode $table)
    {
        $configuration = $this
            ->getJobInstance($code)
            ->getRawParameters();

        $path = dirname($configuration['filePath']);

        foreach ($table->getRows() as $data) {
            copy(__DIR__ . '/fixtures/'. $data[0], rtrim($path, '/') . '/' .$data[0]);
        }
    }

    /**
     * @param int $expectedTotal
     *
     * @Then /^there should be (\d+) family variants?$/
     */
    public function thereShouldBeFamilyVariants($expectedTotal)
    {
        $total = count($this->getFamilyVariantRepository()->findAll());

        Assert::assertEquals($expectedTotal, $total);
    }

    /**
     * @param int $expectedTotal
     *
     * @Then /^there should be (\d+) products?$/
     */
    public function thereShouldBeProducts($expectedTotal)
    {
        $total = $this->getProductRepository()->countAll();

        Assert::assertEquals($expectedTotal, $total);
    }

    /**
     * @param int $expectedTotal
     *
     * @Then /^there should be (\d+) attributes?$/
     */
    public function thereShouldBeAttributes($expectedTotal)
    {
        $total = count($this->getAttributeRepository()->findAll());

        Assert::assertEquals($expectedTotal, $total);
    }

    /**
     * @param int $expectedTotal
     *
     * @Then /^there should be (\d+) categor(?:y|ies)$/
     */
    public function thereShouldBeCategories($expectedTotal)
    {
        $repository = $this->getCategoryRepository();
        $total      = count($repository->findAll());

        Assert::assertEquals($expectedTotal, $total);
    }

    /**
     * @param string    $identifier
     * @param TableNode $table
     *
     * @Given /^the product "([^"]*)" should have the following values?:$/
     */
    public function theProductShouldHaveTheFollowingValues($identifier, TableNode $table)
    {
        $product = $this->getProduct($identifier);

        foreach ($table->getRowsHash() as $rawCode => $value) {
            $infos = $this->getFieldExtractor()->extractColumnInfo($rawCode);

            $attribute = $infos['attribute'];
            $attributeCode = $attribute->getCode();
            $localeCode = $infos['locale_code'];
            $scopeCode = $infos['scope_code'];

            $productValue = $product->getValue($attributeCode, $localeCode, $scopeCode);

            $this->assertProductDataValueEquals($value, $productValue, $attributeCode, $infos);
        }
    }

    /**
     * @param string    $identifier
     * @param TableNode $table
     *
     * @Then /^the product "([^"]*)" should have the following associations?:$/
     */
    public function theProductShouldHaveTheFollowingAssociations($identifier, TableNode $table)
    {
        $this->getMainContext()->getSubcontext('hook')->clearUOW();
        $filter = $this->getContainer()->get('pim_catalog.comparator.filter.product_association');

        $values['associations'] = [];
        foreach ($table->getHash() as $row) {
            if (isset($row['products'])) {
                $values['associations'][$row['type']]['products'] = explode(',', $row['products']);
            }

            if (isset($row['groups'])) {
                $values['associations'][$row['type']]['groups'] = explode(',', $row['groups']);
            }

            if (isset($row['product_models'])) {
                $values['associations'][$row['type']]['product_models'] = explode(',', $row['product_models']);
            }
        }

        Assert::assertEquals([], $filter->filter($this->getProduct($identifier), $values));
    }

    /**
     * @param string    $code
     * @param TableNode $table
     *
     * @Then /^the product model "([^"]*)" should have the following associations?:$/
     */
    public function theProductModelShouldHaveTheFollowingAssociations($code, TableNode $table)
    {
        $this->getMainContext()->getSubcontext('hook')->clearUOW();
        $filter = $this->getContainer()->get('pim_catalog.comparator.filter.product_association');

        $expected['associations'] = [];
        foreach ($table->getHash() as $row) {
            if (isset($row['products'])) {
                $expected['associations'][$row['type']]['products'] = explode(',', $row['products']);
            }

            if (isset($row['groups'])) {
                $expected['associations'][$row['type']]['groups'] = explode(',', $row['groups']);
            }

            if (isset($row['product_models'])) {
                $expected['associations'][$row['type']]['product_models'] = explode(',', $row['product_models']);
            }
        }

        $filtered = $filter->filter($this->getProductModel($code), $expected);
        Assert::assertSame([], $filtered);
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
        Assert::assertEquals($familyCode, $family->getCode());
    }

    /**
     * @param string $productCode
     * @param string $categoryCodes
     *
     *
     * @Given /^(?:the )?categor(?:y|ies) of the product "([^"]*)" should be "([^"]*)"$/
     */
    public function theCategoriesOfTheProductShouldBe($productCode, $categoryCodes)
    {
        $expectedCategories = $this->listToArray($categoryCodes);
        $this->spin(function () use ($productCode, $expectedCategories) {
            $product    = $this->getProduct($productCode);
            $categories = $product->getCategories()->map(
                function ($category) {
                    return $category->getCode();
                }
            )->toArray();
            sort($categories);
            sort($expectedCategories);
            Assert::assertEquals($expectedCategories, $categories);

            return true;
        }, sprintf('Cannot assert that %s categories are %s', $productCode, $categoryCodes));
    }

    /**
     * @param string $productModelCode
     * @param string $categoryCodes
     *
     *
     * @Given /^(?:the )?categor(?:y|ies) of the product model "([^"]*)" should be "([^"]*)"$/
     */
    public function theCategoriesOfTheProductModelShouldBe($productModelCode, $categoryCodes)
    {
        $expectedCategories = $this->listToArray($categoryCodes);
        $this->spin(function () use ($productModelCode, $expectedCategories) {
            $product    = $this->getProductModel($productModelCode);
            $categories = $product->getCategories()->map(
                function ($category) {
                    return $category->getCode();
                }
            )->toArray();
            sort($categories);
            sort($expectedCategories);
            Assert::assertEquals($expectedCategories, $categories);

            return true;
        }, sprintf('Cannot assert that %s categories are %s', $productModelCode, $categoryCodes));
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
        $this->getContainer()->get('pim_catalog.saver.channel')->save($channel);
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
     * @return Group
     *
     * @Then /^there should be a "([^"]+)" user group$/
     */
    public function getUserGroup($userGroupName)
    {
        return $this->spin(function () use ($userGroupName) {
            return $this->getEntityOrException('UserGroup', ['name' => $userGroupName]);
        }, sprintf('Cannot find group %s', $userGroupName));
    }

    /**
     * @param string $userRoleName
     *
     * @return Role
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
     * @return Role
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
     * @return ProductInterface
     */
    public function getProduct($sku)
    {
        $product = $this->spin(function () use ($sku) {
            return $this->getProductRepository()->findOneByIdentifier($sku);
        }, sprintf('Could not find a product with sku "%s"', $sku));

        $this->refresh($product);

        return $product;
    }

    /**
     * @param string $code
     *
     * @throws \InvalidArgumentException
     *
     * @return ProductModelInterface
     */
    public function getProductModel($code)
    {
        $productModel = $this->spin(function () use ($code) {
            return $this->getProductModelRepository()->findOneByIdentifier($code);
        }, sprintf('Could not find a product model with code "%s"', $code));

        return $productModel;
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
     * @param string $localeCode
     *
     * @return LocaleInterface
     */
    public function getLocaleFromCode($localeCode)
    {
        return $this->getEntityOrException('Locale', ['code' => $localeCode]);
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
     * @param int $count
     * @param string $associationType Can be 'group' or 'role'
     *
     * @return bool
     *
     * @throws Spin\TimeoutException
     *
     * @Then /^the user "([^"]+)" should be in (\d+) (group)s?$/
     * @Then /^the user "([^"]+)" should(?: still)? have (\d+) (role)s?$/
     */
    public function checkUserAssociationsCount($username, $count, $associationType)
    {
        $user = $this->getUser($username);
        $this->spin(function () use ($user, $associationType, $count) {
            return $this->getUserAssociationCount($user, $associationType) == $count;
        }, sprintf(
            "Expected %d %s(s) for User %s, found %d",
            $count,
            $associationType,
            $username,
            $this->getUserAssociationCount($user, $associationType)
        ));
    }

    /**
     * @param UserInterface $user
     * @param string $associationType
     *
     * @return int
     */
    private function getUserAssociationCount(UserInterface $user, string $associationType): int
    {
        $this->refresh($user);
        $actualCount = null;
        if ($associationType == 'group') {
            //We remove the "All" group which is not displayed to the user
            $groupNames = $user->getGroupNames();
            if (($key = array_search('All', $groupNames)) !== false) {
                unset($groupNames[$key]);
            }
            $actualCount = count($groupNames);
        } elseif ($associationType == 'role') {
            $actualCount = count($user->getRoles());
        }

        return $actualCount;
    }

    /**
     * @param string $username
     * @param string $locale
     *
     * @return bool
     *
     * @Then /^the user "([^"]+)" should have "([^"]+)" locale$/
     */
    public function checkUserUiLocale($username, $locale)
    {
        $user = $this->getUser($username);
        $this->refresh($user);
        Assert::assertEquals($user->getUiLocale()->getLanguage(), $locale);
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
    public function iHaveRemovedTheAttribute($attribute)
    {
        $remover = $this->getContainer()->get('pim_catalog.remover.attribute');
        $remover->remove($this->getAttribute($attribute));
    }

    /**
     * @param string $identifier
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
        $mountManager = $this->getMountManager();

        $attributeRepository = $this->getContainer()->get('pim_catalog.repository.attribute');

        foreach ($product->getValues() as $value) {
            $attribute = $attributeRepository->findOneByIdentifier($value->getAttributeCode());

            if (in_array($attribute->getType(), [AttributeTypes::IMAGE, AttributeTypes::FILE])) {
                $media = $value->getData();
                if (null !== $media) {
                    $fs = $mountManager->getFilesystem($media->getStorage());
                    $fs->delete($media->getKey());
                }
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

        Assert::assertNotNull($requirement);
        Assert::assertTrue($requirement->isRequired());
    }

    /**
     * @param string $attribute
     * @param string $family
     * @param string $channel
     *
     * @Then /^attribute "([^"]*)" should be optional in family "([^"]*)" for channel "([^"]*)"$/
     */
    public function attributeShouldBeOptionalInFamilyForChannel($attribute, $family, $channel)
    {
        $requirement = $this->getAttributeRequirement($attribute, $family, $channel);

        Assert::assertNotNull($requirement);
        Assert::assertFalse($requirement->isRequired());
    }

    /**
     * @param string $attributes
     * @param string $family
     * @param string $channel
     *
     * @Then /^attributes "([^"]*)" should be optional in family "([^"]*)" for channel "([^"]*)"$/
     */
    public function attributesShouldBeOptionalInFamilyForChannel($attributes, $family, $channel)
    {
        foreach ($this->getMainContext()->listToArray($attributes) as $attribute) {
            $requirement = $this->getAttributeRequirement($attribute, $family, $channel);

            Assert::assertNotNull($requirement);
            Assert::assertFalse($requirement->isRequired());
        }
    }

    /**
     * @param string $identifier
     *
     * @Then /^the history of the product "([^"]*)" has been built$/
     */
    public function theHistoryOfTheProductHasBeenBuilt($identifier)
    {
        $this->buildProductHistory($this->getProduct($identifier));
    }

    /**
     * @param ProductInterface $product
     */
    protected function buildProductHistory(ProductInterface $product)
    {
        $this->getVersionManager()->setRealTimeVersioning(true);
        $versions = $this->getVersionManager()->buildPendingVersions($product);
        foreach ($versions as $version) {
            $this->validate($version);
            $this->getContainer()->get('pim_versioning.saver.version')->save($version);
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
        $repo = $em->getRepository(AttributeRequirement::class);

        $attribute = $this->getAttribute($attributeCode);
        $family    = $this->getFamily($familyCode);
        $channel   = $this->getChannel($channelCode);

        $requirement = $repo->findOneBy(
            [
                'attribute' => $attribute,
                'family'    => $family,
                'channel'   => $channel,
            ]
        );

        $em->refresh($requirement);

        return $requirement;
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
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * We cannot use the product saver to update the product as it automatically updates the product updatedAt date.
     *
     * @Given /^I set the updated date of the (product "([^"]+)") to "([^"]+)"$/
     */
    public function theProductUpdatedDateIs(ProductInterface $product, $identifier, $expected)
    {
        $product->setUpdated(new \DateTime($expected));

        $objectManager = $this->getEntityManager();
        $objectManager->persist($product);
        $objectManager->flush();
    }

    /**
     * Asserts that we have less than a minute interval between the product updated date and the argument
     *
     * @Then /^the (product "([^"]+)") updated date should be close to "([^"]+)"$/
     */
    public function theProductUpdatedDateShouldBeCloseTo(ProductInterface $product, $identifier, $expected)
    {
        Assert::assertLessThan(60, abs(strtotime($expected) - $product->getUpdated()->getTimestamp()));
    }

    /**
     * Asserts that we have more than a minute interval between the product updated date and the argument
     *
     * @Then /^the (product "([^"]+)") updated date should not be close to "([^"]+)"$/
     */
    public function theProductUpdatedDateShouldNotBeCloseTo(ProductInterface $product, $identifier, $expected)
    {
        Assert::assertGreaterThan(60, abs(strtotime($expected) - $product->getUpdated()->getTimestamp()));
    }

    /**
     * @param ProductInterface $owner
     * @param TableNode        $values
     *
     * @Given /^the following associations for the (product "([^"]+)"):$/
     */
    public function theFollowingAssociationsForTheProduct(ProductInterface $owner, TableNode $values)
    {
        $rows = $values->getHash();

        foreach ($rows as $row) {
            $association = $owner->getAssociationForTypeCode($row['type']);

            if (null === $association) {
                $associationType = $this->getContainer()
                    ->get('pim_catalog.repository.association_type')
                    ->findOneBy(['code' => $row['type']]);

                $association = new ProductAssociation();
                $association->setAssociationType($associationType);
                $owner->addAssociation($association);
            }

            $association->addProduct($this->getProduct($row['products']));
        }
        $missingAssociationAdder = $this->getContainer()->get('pim_catalog.association.missing_association_adder');
        $missingAssociationAdder->addMissingAssociations($owner);

        $this->getProductSaver()->save($owner);
    }

    /**
     * @param ProductModelInterface $owner
     * @param TableNode             $values
     *
     * @Given /^the following associations for the (product model "([^"]+)"):$/
     */
    public function theFollowingAssociationsForTheProductModel(ProductModelInterface $owner, TableNode $values)
    {
        $rows = $values->getHash();

        foreach ($rows as $row) {
            $association = $owner->getAssociationForTypeCode($row['type']);

            if (null === $association) {
                $associationType = $this->getContainer()
                    ->get('pim_catalog.repository.association_type')
                    ->findOneBy(['code' => $row['type']]);

                $association = new ProductModelAssociation();
                $association->setAssociationType($associationType);
                $owner->addAssociation($association);
            }

            $association->addProduct($this->getProduct($row['products']));
        }
        $missingAssociationAdder = $this->getContainer()->get('pim_catalog.association.missing_association_adder');
        $missingAssociationAdder->addMissingAssociations($owner);

        $this->getProductModelSaver()->save($owner);
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following clients?:$/
     */
    public function theFollowingClients(TableNode $table)
    {
        $clientManager = $this->getContainer()->get('fos_oauth_server.client_manager.default');
        foreach ($table->getHash() as $data) {
            $client = $clientManager->createClient();
            $client->setLabel($data['label']);
            $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_USER_CREDENTIALS, OAuth2::GRANT_TYPE_REFRESH_TOKEN]);

            $clientManager->updateClient($client);
        }
    }

    /**
     * @param string $identifier
     * @param string $attribute
     * @param string $locale
     * @param string $scope
     *
     * @throws \InvalidArgumentException
     *
     * @return ValueInterface
     */
    protected function getProductValue($identifier, $attribute, $locale = null, $scope = null)
    {
        if (null === $product = $this->getProduct($identifier)) {
            throw new \InvalidArgumentException(sprintf('Could not find product with identifier "%s"', $identifier));
        }

        if (null === $value = $product->getValue($attribute, $locale, $scope)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not find product value for attribute "%s" in locale "%s" for scope "%s for product %s"',
                    $attribute,
                    $locale,
                    $scope,
                    $identifier
                )
            );
        }

        return $value;
    }

    /**
     * @param string $identifier
     * @param string $attribute
     * @param string $locale
     * @param string $scope
     *
     * @throws \InvalidArgumentException
     *
     * @return ValueInterface
     */
    private function getProductModelValue(string $identifier, string $attribute, string $locale = null, string $scope = null)
    {
        if (null === $productModel = $this->getProductModel($identifier)) {
            throw new \InvalidArgumentException(sprintf('Could not find product model with code "%s"', $identifier));
        }

        if (null === $value = $productModel->getValue($attribute, $locale, $scope)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not find product model value for attribute "%s" in locale "%s" for scope "%s"',
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
     *
     * @return GroupTypeInterface
     */
    protected function createGroupType($code, $label)
    {
        $type = new GroupType();
        $type->setCode($code);
        $type->setLocale('en_US')->setLabel($label);

        $this->validate($type);
        $this->getContainer()->get('pim_catalog.saver.group_type')->save($type);

        return $type;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function getAttributeType($type)
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
     * @param array|string $data
     *
     * @return CategoryInterface
     */
    protected function createCategory($data)
    {
        if (is_string($data)) {
            $data = [['code' => $data]];
        }

        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.category');
        $processor = $this->getContainer()->get('pim_connector.processor.denormalization.category');
        $convertedData = $converter->convert($data);
        $category = $processor->process($convertedData);

        $products = $category->getProducts();
        $this->validate($category);
        $this->getContainer()->get('pim_catalog.saver.category')->save($category);

        foreach ($products as $product) {
            $product->addCategory($category);
            $this->getProductSaver()->save($product);
        }

        return $category;
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
        $this->getContainer()->get('pim_user.saver.role')->save($role);

        return $role;
    }

    /**
     * Create an attribute option entity
     *
     * @param string $code
     *
     * @return AttributeOptionInterface
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
                $this->validate($referenceData);
                $this->getContainer()->get('acme_app.saver.color')->save($referenceData);
                break;
            case 'fabric':
            case 'fabrics':
                $referenceData = $this->createFabricReferenceData($code, $label);
                $this->getContainer()->get('acme_app.saver.fabric')->save($referenceData);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown reference data type "%s".', $type));
        }

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
     * @param array              $data
     * @param CommentInterface[] $comments
     *
     * @return \Akeneo\Pim\Enrichment\Component\Comment\Model\CommentInterface
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
            $this->getContainer()->get('pim_comment.saver.comment')->save($parent);
        }

        $this->getContainer()->get('pim_comment.saver.comment')->save($comment);

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
        $this->getContainer()->get('pim_datagrid.saver.datagrid_view')->save($view);

        return $view;
    }

    /**
     * @return FamilyVariantRepositoryInterface
     */
    protected function getFamilyVariantRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.family_variant');
    }

    /**
     * @return \Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface
     */
    protected function getProductRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.product');
    }

    /**
     * @return \Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface
     */
    protected function getProductModelRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.product_model');
    }

    /**
     * @return AttributeRepositoryInterface
     */
    protected function getAttributeRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.attribute');
    }

    /**
     * @return CategoryRepositoryInterface
     */
    protected function getCategoryRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.category');
    }

    /**
     * @return MountManager
     */
    protected function getMountManager()
    {
        return $this->getContainer()->get('oneup_flysystem.mount_manager');
    }

    /**
     * @return EntityWithValuesBuilderInterface
     */
    protected function getProductBuilder()
    {
        return $this->getContainer()->get('pim_catalog.builder.product');
    }

    /**
     * @return SaverInterface
     */
    protected function getProductSaver()
    {
        return $this->getContainer()->get('pim_catalog.saver.product');
    }

    /**
     * @return SaverInterface
     */
    protected function getProductModelSaver()
    {
        return $this->getContainer()->get('pim_catalog.saver.product_model');
    }

    /**
     * @return SaverInterface|BulkSaverInterface
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
     * @return \Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager
     */
    protected function getVersionManager()
    {
        return $this->getContainer()->get('pim_versioning.manager.version');
    }

    /**
     * @return \Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor
     */
    protected function getFieldExtractor()
    {
        return $this
            ->getContainer()
            ->get('pim_connector.array_converter.flat_to_standard.product.attribute_column_info_extractor');
    }

    /**
     * @return \Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface
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
     * Return doctrine manager instance
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * Assert than 2 arrays are equal, regardless the order of the elements.
     *
     * @param $array1
     * @param $array2
     */
    protected function assertArrayEquals($array1, $array2)
    {
        sort($array1);
        sort($array2);
        Assert::assertEquals(join(', ', $array1), join(', ', $array2));
    }

    /**
     * Refresh all the elasticsearch indexes.
     */
    protected function refreshEsIndexes()
    {
        $clients = $this->getMainContext()->getContainer()->get('akeneo_elasticsearch.registry.clients')->getClients();
        foreach ($clients as $client) {
            $client->refreshIndex();
        }
    }

    /**
     * @return Client
     */
    protected function getElasticsearchUserClient()
    {
        return $this->getContainer()->get('akeneo_elasticsearch.client.user');
    }
}
