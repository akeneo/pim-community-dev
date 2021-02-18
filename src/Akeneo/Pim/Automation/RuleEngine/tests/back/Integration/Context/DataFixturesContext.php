<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\RuleEngine\Integration\Context;

use AcmeEnterprise\Bundle\AppBundle\Entity\Color;
use AcmeEnterprise\Bundle\AppBundle\Entity\Fabric;
use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductCsvImport;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleCsvExport;
use Akeneo\Tool\Component\Localization\Localizer\LocalizerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class DataFixturesContext implements Context
{
    private ContainerInterface $container;
    private TransportInterface $transport;

    public function __construct(ContainerInterface $container, TransportInterface $transport)
    {
        $this->container = $container;
        $this->transport = $transport;
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @Given /^I add the "([^"]*)" locale to the "([^"]*)" channel$/
     */
    public function iAddTheLocaleToTheChannel(string $localeCode, string $channelCode)
    {
        $channel = $this->getContainer()->get('pim_catalog.repository.channel')->findOneByIdentifier($channelCode);
        $locale = $this->getContainer()->get('pim_catalog.repository.locale')->findOneByIdentifier($localeCode);

        $channel->addLocale($locale);
        $this->validate($channel);
        $this->getContainer()->get('pim_catalog.saver.channel')->save($channel);
        $this->getContainer()->get('pim_catalog.saver.locale')->save($locale);
    }

    /**
     * @Given /^the following products?:$/
     */
    public function theFollowingProduct(TableNode $table): void
    {
        foreach ($table->getHash() as $data) {
            $this->createProduct($data);
        }
        $this->purgeMessenger();
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

        $this->container->get('doctrine.orm.entity_manager')->refresh($product);
        $this->getProductSaver()->save($product);
    }

    /**
     * @Given /the following family variants?:/
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
     * @Given the root product model :productModel with family variant :variantFamily
     */
    public function createRootProductModel(string $productModelCode, string $variantFamilyCode)
    {
        $productModel = $this->getContainer()->get('pim_catalog.factory.product_model')->create();
        $this->getContainer()->get('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'code' => $productModelCode,
                'parent' => null,
                'family_variant' => $variantFamilyCode,
            ]
        );

        $this->validate($productModel);
        $this->getContainer()->get('pim_catalog.saver.product_model')->save($productModel);
    }

    /**
     * @param array|string $data
     *
     * @Given /^a "([^"]*)" product$/
     */
    public function createProduct($data): ProductInterface
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

        if (isset($data['parent']) && empty($data['parent'])) {
            unset($data['parent']);
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
     * @Given /^the following product values?:$/
     */
    public function theFollowingProductValues(TableNode $table): void
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

        $this->purgeMessenger();
    }

    /**
     * @Given I set the :code attribute in read only
     */
    public function setTheAttributeInReadOny(string $code): void
    {
        $attribute = $this->getContainer()->get('pim_catalog.repository.attribute')->findOneByIdentifier($code);
        $attribute->setProperty('is_read_only', true);
        $this->getContainer()->get('pim_catalog.saver.attribute')->save($attribute);
    }

    /**
     * @Given /^the following product rule definitions:$/
     */
    public function theFollowingProductRuleDefinitions(PyStringNode $string): void
    {
        $string = $this->replacePlaceholders($string->getRaw());
        $definitions = Yaml::parse($string);

        foreach ($definitions as $key => $definition) {
            $definition['code'] = $key;

            $ruleDefinition = $this->container
                ->get('pimee_catalog_rule.processor.denormalization.rule_definition')
                ->process($definition);
            $this->container->get('akeneo_rule_engine.saver.rule_definition')->save($ruleDefinition);
        }
    }

    /**
     * @Given the family :familyCode has the attributes :attributeCodes
     */
    public function theFamilyHasTheAttributes(string $familyCode, string $attributeCodes): void
    {
        $family = $this->getContainer()->get('pim_catalog.repository.family')->findOneByIdentifier($familyCode);
        Assert::notNull($family, sprintf('The family "%s" does not exist.', $familyCode));

        $attributeRepository = $this->getContainer()->get('pim_catalog.repository.attribute');
        foreach ($this->listToArray($attributeCodes) as $attributeCode) {
            $attribute = $attributeRepository->findOneByIdentifier($attributeCode);
            Assert::notNull($attribute, sprintf('The attribute "%s" does not exist.', $attributeCode));
            $family->addAttribute($attribute);
        }

        $this->getContainer()->get('pim_catalog.saver.family')->save($family);
    }

    /**
     * @Given /^the following attributes?:$/
     */
    public function theFollowingAttributes(TableNode $table): void
    {
        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.attribute');
        $processor = $this->getContainer()->get('pim_connector.processor.denormalization.attribute');
        $saver = $this->getContainer()->get('pim_catalog.saver.attribute');

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
     * @Given /^the following "([^"]*)" attribute options?: (.*)$/
     */
    public function theFollowingAttributeOptions(string $attributeCode, string $rawOptions): void
    {
        $attribute = $this->getContainer()->get('pim_catalog.repository.attribute')->findOneByIdentifier(
            strtolower($attributeCode)
        );
        $options = [];
        foreach ($this->listToArray($rawOptions) as $optionCode) {
            $option = new AttributeOption();
            $option->setCode($optionCode);
            $attribute->addOption($option);
            $this->validate($option);
            $options[] = $option;
        }

        $this->getContainer()->get('pim_catalog.saver.attribute_option')->saveAll($options);
    }

    /**
     * @Then /^the history of the product "([^"]*)" has been built$/
     */
    public function theHistoryOfTheProductHasBeenBuilt($identifier)
    {
        $product = $this->getContainer()->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);

        $this->getVersionManager()->setRealTimeVersioning(true);
        $versions = $this->getVersionManager()->buildPendingVersions($product);
        foreach ($versions as $version) {
            $this->validate($version);
            $this->getContainer()->get('pim_versioning.saver.version')->save($version);
        }
    }

    /**
     * @Given /^the following reference data?:$/
     */
    public function theFollowingReferenceData(TableNode $table): void
    {
        foreach ($table->getHash() as $row) {
            $this->createReferenceData(trim($row['type']), trim($row['code']), trim($row['label'] ?? ''));
        }
    }

    /**
     * @Given the following family:
     */
    public function theFollowingFamily(TableNode $table): void
    {
        $familyFactory = $this->getContainer()->get('pim_catalog.factory.family');
        $attributeRepository = $this->getContainer()->get('pim_catalog.repository.attribute');
        $familyUpdater = $this->getContainer()->get('pim_catalog.updater.family');
        $familySaver = $this->getContainer()->get('pim_catalog.saver.family');
        foreach ($table->getHash() as $familyData) {
            $family = $familyFactory->create();

            $attributeCodes = explode(',', $familyData['attributes']);
            foreach ($attributeCodes as $attributeCode) {
                $attribute = $attributeRepository->findOneByIdentifier($attributeCode);
                Assert::notNull($attribute, sprintf('Attribute "%s" does not exist', $attributeCode));
            }
            $familyData['attributes'] = $attributeCodes;

            if (isset($familyData['label-en_US'])) {
                $familyData['labels'] = ['en_US' => $familyData['label-en_US']];
                unset($familyData['label-en_US']);
            }

            if (isset($familyData['attribute_requirements'])) {
                $requirements = [];
                $normalizedRequirements = explode(',', $familyData['attribute_requirements']);
                foreach ($normalizedRequirements as $normalizedRequirement) {
                    $chunks = explode('-', $normalizedRequirement);
                    if (!isset($requirements[$chunks[0]])) {
                        $requirements[$chunks[0]] = [];
                    }
                    $requirements[$chunks[0]][] = $chunks[1];
                }
                $familyData['attribute_requirements'] = $requirements;
            }

            $familyUpdater->update($family, $familyData);
            $familySaver->save($family);
        }
    }

    /**
     * @Given /^the following categories:$/
     */
    public function theFollowingCategories(TableNode $table): void
    {
        $categoryRepository = $this->getContainer()->get('pim_catalog.repository.category');
        $categorySaver = $this->getContainer()->get('pim_catalog.saver.category');
        foreach ($table->getHash() as $data) {
            $category = new Category();
            $category->setCode($data['code']);
            if (isset($data['parent'])) {
                $parentCategory = $categoryRepository->findOneByIdentifier($data['parent']);
                $category->setParent($parentCategory);
            }

            $categorySaver->save($category);
        }
    }

    /**
     * @Given /^the following root product models?:$/
     */
    public function theFollowingRootProductModels(TableNode $table): void
    {
        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.product_model');
        $productModelUpdater = $this->getContainer()->get('pim_catalog.updater.product_model');

        foreach ($table->getHash() as $data) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replacePlaceholders($value);
            }

            $productModel = new ProductModel();
            $convertedData = $converter->convert($data);
            $productModelUpdater->update($productModel, $convertedData);

            $errors = $this->getContainer()->get('pim_catalog.validator.product_model')->validate($productModel);
            if (0 !== $errors->count()) {
                throw new \LogicException('Product model could not be updated, invalid data provided.');
            }

            $this->getContainer()->get('pim_catalog.saver.product_model')->save($productModel);

            $uniqueAxesCombinationSet = $this->getContainer()->get('pim_catalog.validator.unique_axes_combination_set');
            $uniqueAxesCombinationSet->reset();

            $this->getContainer()->get('pim_connector.doctrine.cache_clearer')->clear();
            $this->refreshEsIndexes();
        }
        $this->purgeMessenger();
    }

    /**
     * @Given /^the following sub product models?:$/
     */
    public function theFollowingSubProductModels(TableNode $table): void
    {
        $converter = $this->getContainer()->get('pim_connector.array_converter.flat_to_standard.product_model');
        $productModelUpdater = $this->getContainer()->get('pim_catalog.updater.product_model');

        foreach ($table->getHash() as $data) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replacePlaceholders($value);
            }

            $productModel = new ProductModel();
            $convertedData = $converter->convert($data);
            $productModelUpdater->update($productModel, $convertedData);

            $errors = $this->getContainer()->get('pim_catalog.validator.product_model')->validate($productModel);
            if (0 !== $errors->count()) {
                throw new \LogicException('Product model could not be updated, invalid data provided.');
            }

            $this->getContainer()->get('pim_catalog.saver.product_model')->save($productModel);

            $uniqueAxesCombinationSet = $this->getContainer()->get('pim_catalog.validator.unique_axes_combination_set');
            $uniqueAxesCombinationSet->reset();

            $this->getContainer()->get('pim_connector.doctrine.cache_clearer')->clear();
            $this->refreshEsIndexes();
        }
        $this->purgeMessenger();
    }

    /**
     * @Given /^the following jobs?:$/
     */
    public function theFollowingJobs(TableNode $table): void
    {
        $jobInstanceSaver = $this->getContainer()->get('akeneo_batch.saver.job_instance');

        foreach ($table->getHash() as $data) {
            $jobInstance = new JobInstance($data['connector'], $data['type'], $data['alias']);
            $jobInstance->setCode($data['code']);
            $jobInstance->setLabel($data['label']);
            $jobInstanceSaver->save($jobInstance);
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function validate($object): void
    {
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
                    get_class($object),
                    implode(', ', $messages)
                )
            );
        }
    }

    protected function buildProductHistory(ProductInterface $product): void
    {
        $this->getVersionManager()->setRealTimeVersioning(true);
        $versions = $this->getVersionManager()->buildPendingVersions($product);
        foreach ($versions as $version) {
            $this->validate($version);
            $this->getContainer()->get('pim_versioning.saver.version')->save($version);
        }
    }

    protected function createReferenceData(string $type, string $code, string $label): ReferenceDataInterface
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

    protected function createColorReferenceData(string $code, string $label): Color
    {
        $color = new Color();
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

    protected function createFabricReferenceData(string $code, string $label): Fabric
    {
        $fabric = new Fabric();
        $fabric->setCode($code);
        $fabric->setName($label);

        return $fabric;
    }

    protected function getProductSaver(): SaverInterface
    {
        return $this->getContainer()->get('pim_catalog.saver.product');
    }

    protected function getVersionManager(): VersionManager
    {
        return $this->getContainer()->get('pim_versioning.manager.version');
    }

    protected function refreshEsIndexes(): void
    {
        $clients = $this->getContainer()->get('akeneo_elasticsearch.registry.clients')->getClients();
        foreach ($clients as $client) {
            $client->refreshIndex();
        }
    }

    private function replacePlaceholders(string $string): string
    {
        $kernelRootDir = $this->getContainer()->getParameter('kernel.root_dir');

        return strtr($string, [
            '%tmp%' => getenv('BEHAT_TMPDIR') ?: '/tmp/pim-behat',
            '%fixtures%' => $kernelRootDir . '/../tests/legacy/features/Context/fixtures',
            '%web%' => $kernelRootDir . '/../public/',
        ]);
    }

    private function listToArray(string $list): array
    {
        return array_map('trim', explode(',', $list));
    }

    private function purgeMessenger(): void
    {
        while (!empty($envelopes = $this->transport->get())) {
            foreach ($envelopes as $envelope) {
                $this->transport->ack($envelope);
            }
        }
    }
}
