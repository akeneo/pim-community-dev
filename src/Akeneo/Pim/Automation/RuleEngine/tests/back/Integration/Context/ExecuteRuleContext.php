<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\RuleEngine\Integration\Context;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\PriceCollectionValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\BuilderInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\ChainedRunner;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ExecuteRuleContext implements Context
{
    /** @var array */
    private $productModelFields = ['code', 'parent', 'categories', 'family_variant'];

    public function __construct(
        private Connection $connection,
        private ProductRepositoryInterface $productRepository,
        private ProductModelRepositoryInterface $productModelRepository,
        private ChainedRunner $rulesRunner,
        private RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        private BuilderInterface $builder,
        private AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        private GetProductCompletenesses $getProductCompletenesses,
        private FamilyVariantRepositoryInterface $familyVariantRepository,
        private EntityManagerClearerInterface $clearer
    ) {
    }

    /**
     * @When /^the product rule "([^"]*)" is executed$/
     * @When /^the "([^"]*)" product rule is executed$/
     */
    public function theProductRuleIsExecuted(string $ruleCode): void
    {
        $rule = $this->getRule($ruleCode);
        $this->clearer->clear();

        $this->rulesRunner->run($rule);
    }

    /**
     * We use the tag |NL| for \n in gherkin
     *
     * @Then /^the ((?!product)\w+) (\w+) (\w+) of "([^"]*)" should be "(.*)"$/
     */
    public function theScopableAndLocalizableOfShouldBe($locale, $scope, $attribute, $identifier, $value)
    {
        $locale = 'unlocalized' === $locale ? null : $locale;
        $scope = 'unscoped' === $scope ? null : $scope;
        $value = str_replace('|NL|', "\n", $value);

        $entityWithValues = $this->productRepository->findOneByIdentifier($identifier);
        if (null === $entityWithValues) {
            $entityWithValues = $this->productModelRepository->findOneByIdentifier($identifier);
        }
        Assert::notNull($entityWithValues);
        $entityValue = $entityWithValues->getValue($attribute, $locale, $scope);

        if (null === $entityValue && '' === $value) {
            return;
        }

        Assert::notNull($entityValue, 'The value is empty for this product.');
        Assert::same($entityValue->__toString(), $value);
    }

    /**
     * @Then /^the product "([^"]*)" should have the following values?:$/
     * @Then /^the "([^"]*)" product should have the following values?:$/
     */
    public function theProductShouldHaveTheFollowingValues(string $identifier, TableNode $table)
    {
        $product = $this->getProduct($identifier);

        foreach ($table->getRowsHash() as $rawCode => $value) {
            $infos = $this->attributeColumnInfoExtractor->extractColumnInfo($rawCode);

            $attribute = $infos['attribute'];
            $attributeCode = $attribute->getCode();
            $localeCode = $infos['locale_code'];
            $scopeCode = $infos['scope_code'];

            $productValue = $product->getValue($attributeCode, $localeCode, $scopeCode);

            $this->assertProductDataValueEquals($value, $productValue, $attributeCode, $infos);
        }
    }

    /**
     * @Then /^product "([^"]*)" should be enabled$/
     */
    public function productShouldBeEnabled(string $identifier): void
    {
        $product = $this->getProduct($identifier);
        Assert::true($product->isEnabled(), 'Product was expected to be be enabled');
    }

    /**
     * @Then /^product "([^"]*)" should be disabled$/
     */
    public function productShouldBeDisabled(string $identifier): void
    {
        $product = $this->getProduct($identifier);
        Assert::false($product->isEnabled(), 'Product was expected to be be disabled');
    }

    /**
     * @Then /^(?:the )?categor(?:y|ies) of the "([^"]*)" product should be "([^"]*)"$/
     */
    public function theCategoriesOfTheProductShouldBe(string $productCode, string $categoryCodes): void
    {
        $expectedCategoryCodes = array_map('trim', explode(',', $categoryCodes));
        $product = $this->getProduct($productCode);
        $productCategoryCodes = $product->getCategories()->map(
            function ($category) {
                return $category->getCode();
            }
        )->toArray();

        sort($productCategoryCodes);
        sort($expectedCategoryCodes);
        Assert::same(
            $productCategoryCodes,
            $expectedCategoryCodes,
            sprintf('Cannot assert that %s categories are %s', $productCode, $categoryCodes)
        );
    }

    /**
     * @Then /^(?:the )?categor(?:y|ies) of the "([^"]*)" product model should be "([^"]*)"$/
     */
    public function theCategoriesOfTheProductModelShouldBe(string $productModelCode, string $categoryCodes): void
    {
        $expectedCategoryCodes = array_map('trim', explode(',', $categoryCodes));
        $productModel = $this->getProductModel($productModelCode);
        $categories = $productModel->getCategories()->map(function ($category) {
            return $category->getCode();
        })->toArray();

        sort($categories);
        sort($expectedCategoryCodes);
        Assert::same(
            $categories,
            $expectedCategoryCodes,
            sprintf('Cannot assert that %s categories are %s', $productModelCode, $categoryCodes)
        );
    }

    /**
     * @Then /^the completeness for the product "([^"]*)" should be?:$/
     */
    public function theCompletenessForTheProductShouldBe(string $identifier, TableNode $table): void
    {
        $product = $this->getProduct($identifier);
        $completenessCollection = $this->getProductCompletenesses->fromProductUuid($product->getUuid());

        foreach ($table as $index => $expected) {
            $foundCompleteness = null;

            /** @var ProductCompleteness $completeness */
            foreach ($completenessCollection as $completeness) {
                if (
                    $expected['channel'] === $completeness->channelCode()
                    && $expected['locale'] === $completeness->localeCode()
                ) {
                    $foundCompleteness = $completeness;

                    break;
                }
            }

            Assert::notNull($foundCompleteness, sprintf(
                'The completeness is not found for the "%s" locale and the "%s" channel.',
                $expected['channel'],
                $expected['locale']
            ));

            if (array_key_exists('missing_values', $expected)) {
                Assert::same($foundCompleteness->missingCount(), (int) $expected['missing_values'], sprintf(
                    'The completeness missing count must be "%d", having "%d" for the "%s" locale and the "%s" channel.',
                    $expected['missing_values'],
                    $foundCompleteness->missingCount(),
                    $expected['channel'],
                    $expected['locale']
                ));
            }

            if (array_key_exists('ratio', $expected)) {
                $expected['ratio'] = str_replace('%', '', $expected['ratio']);
                Assert::same($foundCompleteness->ratio(), (int) $expected['ratio'], sprintf(
                    'The completeness missing count must be "%d", having "%d" for the "%s" locale and the "%s" channel.',
                    $expected['ratio'],
                    $foundCompleteness->ratio(),
                    $expected['channel'],
                    $expected['locale']
                ));
            }
        }
    }

    /**
     * @Then /^there should be the following (?:|root product model|product model):$/
     */
    public function thereShouldBeTheFollowingProduct(TableNode $properties): void
    {
        foreach ($properties->getHash() as $rawProductModel) {
            $productModel = $this->getProductModel($rawProductModel['code']);

            foreach ($rawProductModel as $propertyName => $value) {
                if (in_array($propertyName, $this->productModelFields)) {
                    $this->checkProductModelField($productModel, $propertyName, $value);
                } else {
                    $this->checkProductModelValue($productModel, $propertyName, $value);
                }
            }
        }
    }

    /**
     * @Then the :identifier product model should not have the following values :attributesCodes
     */
    public function theProductShouldNotHaveTheFollowingValues(string $identifier, string $attributesCodes): void
    {
        $attributesCodes = array_map('trim', explode(',', $attributesCodes));
        $productModel = $this->getProductModel($identifier);

        foreach ($attributesCodes as $propertyName) {
            $infos = $this->attributeColumnInfoExtractor->extractColumnInfo($propertyName);
            /** @var AttributeInterface $attribute */
            $attribute = $infos['attribute'];
            $productValue = $productModel->getValuesForVariation()->getByCodes(
                $attribute->getCode(),
                $infos['locale_code'],
                $infos['scope_code']
            );

            Assert::null($productValue, sprintf('The value "%s" for "%s" product model exists', $attribute->getCode(), $identifier));
        }
    }

    /**
     * @Then /^the "([^"]*)" variant product should not have the following values?:$/
     */
    public function theVariantProductShouldNotHaveTheFollowingValues(string $identifier, TableNode $table): void
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);

        foreach ($table->getRowsHash() as $rawCode => $value) {
            $infos = $this->attributeColumnInfoExtractor->extractColumnInfo($rawCode);

            $attribute = $infos['attribute'];
            $attributeCode = $attribute->getCode();
            $productValue = $product->getValuesForVariation()->getByCodes($attributeCode, $infos['locale_code'], $infos['scope_code']);

            Assert::null($productValue, sprintf('Product value for "%s" product exists', $identifier));
        }
    }

    private function getProduct(string $identifier): ProductInterface
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        if (null === $product) {
            throw new \RuntimeException(sprintf('Product with identifier "%s" is not found.', $identifier));
        }

        return $product;
    }

    private function getProductModel(string $identifier): ProductModelInterface
    {
        $productModel = $this->productModelRepository->findOneByIdentifier($identifier);
        Assert::notNull(sprintf('The "%s" product model does not exist', $identifier));

        return $productModel;
    }

    private function getRule(string $ruleDefinitionIdentifier): RuleInterface
    {
        $definition = $this->ruleDefinitionRepository->findOneByIdentifier($ruleDefinitionIdentifier);
        Assert::notNull($definition, sprintf('Rule definition with the "%s" identifier was not found.', $ruleDefinitionIdentifier));

        return $this->builder->build($definition);
    }

    protected function assertProductDataValueEquals(
        $value,
        ?ValueInterface $productValue,
        string $attributeCode,
        $infos = []
    ): void {
        $backendType = $this->getAttributeBackendType($attributeCode);

        $priceCurrency = $infos['price_currency'] ?? null;

        if ('' === $value || '**empty**' === $value) {
            Assert::null($productValue, sprintf(
                'Expected value of attribute "%s" to be null, "%s" found.',
                $attributeCode,
                (string) $productValue
            ));
        } elseif ('media' === $backendType) {
            // media filename is auto generated during media handling and cannot be guessed
            // (it contains a timestamp)
            Assert::true(
                null !== $productValue->getData() &&
                    false !== strpos($productValue->getData()->getOriginalFilename(), $value)
            );
        } elseif ('prices' === $backendType && null !== $priceCurrency) {
            // $priceCurrency can be null if we want to test all the currencies at the same time
            // in this case, it's a simple string comparison
            // example: 180.00 EUR, 220.00 USD

            Assert::implementsInterface($productValue, PriceCollectionValueInterface::class);
            $price = $productValue->getPrice($priceCurrency);

            if ($value === null) {
                Assert::null($price);
            } else {
                Assert::same((float) $value, (float) $price->getData());
            }
        } elseif ('boolean' === $backendType) {
            $value = $value === "false" ? false : $value;
            $value = $value === "true" ? true : $value;
            Assert::same((bool) $value, (bool) $productValue->getData());
        } elseif ('date' === $backendType) {
            Assert::same($value, $productValue->getData()->format('Y-m-d'));
        } elseif ('decimal' === $backendType) {
            Assert::same((float) $value, (float) $productValue->getData());
        } elseif ('option' === $backendType) {
            Assert::same($value, $productValue->getData());
        } elseif ('metric' === $backendType) {
            Assert::same((float) $value, (float) $productValue->getData()->getData());
        } elseif ('text' === $backendType) {
            Assert::same((string) $value, (string) $productValue->getData());
        } else {
            Assert::same($value, (string) $productValue);
        }
    }

    protected function getAttributeBackendType(string $attributeCode): string
    {
        $sql = "SELECT backend_type FROM pim_catalog_attribute WHERE code = :attribute_code";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue("attribute_code", $attributeCode);

        return $stmt->executeQuery()->fetchOne();
    }

    private function checkProductModelField(ProductModelInterface $productModel, string $propertyName, $value): void
    {
        switch ($propertyName) {
            case 'code':
                break;
            case 'parent':
                $actualParent = $productModel->getParent();
                $expectedParent = $this->productModelRepository->findOneByIdentifier($value);

                Assert::same($actualParent, $expectedParent);
                break;
            case 'family_variant':
                $actualFamilyVariant = $productModel->getFamilyVariant();
                $expectedFamilyVariant = $this->familyVariantRepository->findOneByIdentifier($value);

                Assert::same($actualFamilyVariant, $expectedFamilyVariant);
                break;
            case 'categories':
                $actualCategoryCodes = $productModel->getCategoryCodes();
                $expectedCategoryCodes = explode(',', $value);

                Assert::same($actualCategoryCodes, $expectedCategoryCodes);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('The "%s" property name is unknown.', $propertyName));
        }
    }

    private function checkProductModelValue(ProductModelInterface $productModel, string $propertyName, $value): void
    {
        $infos = $this->attributeColumnInfoExtractor->extractColumnInfo($propertyName);

        $attribute = $infos['attribute'];
        $priceCurrency = isset($infos['price_currency']) ? $infos['price_currency'] : null;
        $productValue = $productModel->getValue(
            $attribute->getCode(),
            $infos['locale_code'],
            $infos['scope_code']
        );

        if ('' === $value) {
            Assert::isEmpty((string) $productValue);
        } elseif ('prices' === $attribute->getBackendType() && null !== $priceCurrency) {
            // $priceCurrency can be null if we want to test all the currencies at the same time
            // in this case, it's a simple string comparison
            // example: 180.00 EUR, 220.00 USD

            Assert::implementsInterface($productValue, PriceCollectionValueInterface::class);
            $price = $productValue->getPrice($priceCurrency);
            Assert::eq($price->getData(), $value);
        } elseif ('date' === $attribute->getBackendType()) {
            Assert::eq($productValue->getData()->format('Y-m-d'), $value);
        } else {
            Assert::eq((string) $productValue, $value);
        }
    }
}
