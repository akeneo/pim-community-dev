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
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\BuilderInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Runner\ChainedRunner;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Doctrine\DBAL\Driver\Connection;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ExecuteRuleContext implements Context
{
    /** @var Connection */
    private $connection;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ChainedRunner */
    private $rulesRunner;

    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var BuilderInterface */
    private $builder;

    /** @var AttributeColumnInfoExtractor */
    private $attributeColumnInfoExtractor;

    /** @var GetProductCompletenesses */
    private $getProductCompletenesses;

    public function __construct(
        Connection $connection,
        ProductRepositoryInterface $productRepository,
        ChainedRunner $rulesRunner,
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        BuilderInterface $builder,
        AttributeColumnInfoExtractor $attributeColumnInfoExtractor,
        GetProductCompletenesses $getProductCompletenesses
    ) {
        $this->connection = $connection;
        $this->productRepository = $productRepository;
        $this->rulesRunner = $rulesRunner;
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->builder = $builder;
        $this->attributeColumnInfoExtractor = $attributeColumnInfoExtractor;
        $this->getProductCompletenesses = $getProductCompletenesses;
    }

    /**
     * @Given /^the product rule "([^"]*)" is executed$/
     */
    public function theProductRuleIsExecuted(string $ruleCode): void
    {
        $rule = $this->getRule($ruleCode);

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

        $product = $this->productRepository->findOneByIdentifier($identifier);
        $productValue = $product->getValue($attribute, $locale, $scope);

        if (null === $productValue && '' === $value) {
            return;
        }

        Assert::notNull($productValue, 'The value is empty for this product.');
        Assert::same($value, $productValue->__toString());
    }

    /**
     * @Given /^the product "([^"]*)" should have the following values?:$/
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
     * @Given /^product "([^"]*)" should be enabled$/
     */
    public function productShouldBeEnabled(string $identifier): void
    {
        $product = $this->getProduct($identifier);
        Assert::true($product->isEnabled(), 'Product was expected to be be enabled');
    }

    /**
     * @Given /^product "([^"]*)" should be disabled$/
     */
    public function productShouldBeDisabled(string $identifier): void
    {
        $product = $this->getProduct($identifier);
        Assert::false($product->isEnabled(), 'Product was expected to be be disabled');
    }

    /**
     * @Given /^(?:the )?categor(?:y|ies) of the product "([^"]*)" should be "([^"]*)"$/
     */
    public function theCategoriesOfTheProductShouldBe(string $productCode, string $categoryCodes): void
    {
        $expectedCategoryCodes = explode(',', $categoryCodes);
        $expectedCategoryCodes = array_map('trim', $expectedCategoryCodes);
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
     * @Then /^the completeness for the product "([^"]*)" should be?:$/
     */
    public function theCompletenessForTheProductShouldBe(string $identifier, TableNode $table): void
    {
        $product = $this->getProduct($identifier);
        $completenessCollection = $this->getProductCompletenesses->fromProductId($product->getId());

        foreach ($table as $index => $expected) {
            $foundCompleteness = null;

            /** @var ProductCompleteness $completeness */
            foreach ($completenessCollection as $completeness) {
                if ($expected['channel'] === $completeness->channelCode()
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

    private function getProduct(string $identifier): ProductInterface
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        if (null === $product) {
            throw new \RuntimeException(sprintf('Product with identifier "%s" is not found.', $identifier));
        }

        return $product;
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
        $stmt->execute();

        return $stmt->fetch()['backend_type'];
    }
}
