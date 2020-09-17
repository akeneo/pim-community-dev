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

namespace Akeneo\Test\Pim\Automation\RuleEngine\Acceptance\Context;

use Akeneo\Pim\Automation\RuleEngine\Component\Connector\Executor\RulesExecutor;
use Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Denormalization\RuleDefinitionProcessor;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsUpdater;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier\ProductsValidator;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Test\Pim\Automation\RuleEngine\Common\Context\ExceptionContext;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\BuilderInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ExecuteRuleContext implements Context
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var RulesExecutor */
    private $rulesExecutor;

    /** @var RuleDefinitionProcessor */
    private $ruleDefinitionProcessor;

    /** @var ProductsUpdater */
    private $productsUpdater;

    /** @var ProductsValidator */
    private $productsValidator;

    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var BuilderInterface */
    private $builder;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        RulesExecutor $rulesExecutor,
        RuleDefinitionProcessor $ruleDefinitionProcessor,
        ProductsUpdater $productsUpdater,
        ProductsValidator $productsValidator,
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        BuilderInterface $builder
    ) {
        $this->productRepository = $productRepository;
        $this->rulesExecutor = $rulesExecutor;
        $this->ruleDefinitionProcessor = $ruleDefinitionProcessor;
        $this->productsUpdater = $productsUpdater;
        $this->productsValidator = $productsValidator;
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->builder = $builder;
    }

    /**
     * @Given /^A rule with concatenate action with two text fields$/
     */
    public function aRuleWithConcatenateActionWithTwoTextFields(): void
    {
        $rulesConfig = <<<YAML
rules:
    concatenate_rule:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: concatenate
              from:
                  - field: pim_brand
                  - field: name
                    locale: en_US
              to:
                  field: description
                  scope: ecommerce
                  locale: en_US
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When /^I execute the concatenate rule on products$/
     */
    public function executeTheConcatenateRuleOnProducts(): void
    {
        $rule = $this->getRule('concatenate_rule');

        $this->executeRulesOnSubjects([$rule], $this->productRepository->findAll());
    }

    /**
     * @Then /^the product "([^"]*)" is successfully updated by the concatenate rule with two text fields$/
     */
    public function theProductIsSuccessfullyUpdated(string $identifier): void
    {
        $product = $this->getProduct($identifier);
        $rule = $this->getRule('concatenate_rule');
        EventSubscriberContext::assertNoSkipExecutionForRuleAndEntity($rule, $product);

        $descriptionValue = $product->getValue('description', 'en_US', 'ecommerce');
        Assert::same($descriptionValue->getData(), 'Crown Bolt 75024');
    }

    /**
     * @Given /^A rule that concatenates given attribute values to a text attribute value$/
     */
    public function aRuleThatConcatenatesGivenAttributeValuesToATextAttributeValue(): void
    {
        $rulesConfig = <<<YAML
rules:
    concatenate_rule:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: concatenate
              from:
                  - field: pim_brand
                  - field: description
                    scope: ecommerce
                    locale: en_US
                  - field: sku
                  - field: processor
                    locale: en_US
                  - field: price
                    currency: EUR
                  - field: release_date
                  - field: relEASE_DAte
                    format: d/m/Y
                  - field: weight
                  - field: color
                  - field: color
                    label_locale: fr_FR
                  - field: connectivity
                  - field: connectivity
                    label_locale: fr_FR
                  - field: designer
                  - field: designer
                    label_locale: fr_FR
                  - field: designers
                  - field: designers
                    label_locale: fr_FR
              to:
                  field: name
                  locale: en_US
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @Then /^the ((?!product)\w+) (\w+) (\w+) of "([^"]*)" should be "(.*)"$/
     */
    public function theScopableAndLocalizableOfShouldBe($locale, $scope, $attribute, $identifier, $value)
    {
        $locale = 'unlocalized' === $locale ? null : $locale;
        $scope = 'unscoped' === $scope ? null : $scope;
        $value = str_replace('|NL|', "\n", $value);

        $product = $this->getProduct($identifier);
        $productValue = $product->getValue($attribute, $locale, $scope);
        if (null === $productValue && '' === $value) {
            return;
        }

        Assert::notNull($productValue, 'The value is empty for this product.');
        Assert::same($productValue->__toString(), $value);
    }

    /**
     * @Then /^there should be no ((?!product)\w+) (\w+) (\w+) value for the product "([^"]*)"$/
     */
    public function thereShouldBeNoValueForTheProduct($locale, $scope, $attribute, $identifier)
    {
        $locale = 'unlocalized' === $locale ? null : $locale;
        $scope = 'unscoped' === $scope ? null : $scope;

        $product = $this->getProduct($identifier);
        $productValue = $product->getValue($attribute, $locale, $scope);

        Assert::null($productValue, 'The value exists for this product.');
    }

    /**
     * @Given /^A rule that concatenates given attribute values to a textarea attribute value$/
     */
    public function aRuleThatConcatenatesGivenAttributeValuesToATextareaAttributeValue(): void
    {
        $rulesConfig = <<<YAML
rules:
    concatenate_rule:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: concatenate
              from:
                  - field: pim_brand
                  - field: name
                    locale: en_US
                  - field: sku
                  - field: processor
                    locale: en_US
                  - field: price
                    currency: EUR
                  - field: release_date
                  - field: relEASE_DAte
                    format: d/m/Y
                  - field: weight
                  - field: sub_description
                  - field: color
                  - field: color
                    label_locale: fr_FR
                  - new_line: ~
                  - text: 'A text:'
                  - field: connectivity
                  - field: connectivity
                    label_locale: fr_FR
              to:
                  field: description
                  scope: ecommerce
                  locale: en_US
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @Given A rule that concatenates rich textarea attribute value to a simple textarea attribute value
     */
    public function aRuleThatConcatenatesRichTextareaAttributeValueToASimpleTextareaAttributeValue(): void
    {
        $rulesConfig = <<<YAML
rules:
    concatenate_rule:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: concatenate
              from:
                  - text: 'Here is the result of the concatenate:'
                  - new_line: ~
                  - field: pim_brand
                  - field: description
                    scope: ecommerce
                    locale: en_US
              to:
                  field: sub_description
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @Given A rule that concatenates simple textarea attribute value to a rich textarea attribute value
     */
    public function aRuleThatConcatenatesSimpleTextareaAttributeValueToARichTextareaAttributeValue(): void
    {
        $rulesConfig = <<<YAML
rules:
    concatenate_rule:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: concatenate
              from:
                  - text: 'Here is the result of the concatenate:'
                  - new_line: ~
                  - field: pim_brand
                  - field: sub_description
              to:
                  field: description
                  scope: ecommerce
                  locale: en_US
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @Given Rules with following configuration:
     */
    public function aRuleWithFollowingConfiguration(PyStringNode $rulesConfiguration): void
    {
        $this->importRules($rulesConfiguration->getRaw());
    }

    /**
     * @When I execute the ":ruleName" rule on products
     */
    public function executeTheRuleOnProducts(string $ruleName): void
    {
        $rule = $this->getRule($ruleName);
        $this->executeRulesOnSubjects([$rule], $this->productRepository->findAll());
    }

    /**
     * @When I execute the clear rule on products
     */
    public function executeTheClearRuleOnProducts(): void
    {
        $rule = $this->getRule('clear_rule');
        $this->executeRulesOnSubjects([$rule], $this->productRepository->findAll());
    }

    /**
     * @Then the product :identifier should not have any category
     */
    public function theProductShouldNotHaveAnyCategory(string $identifier): void
    {
        $productCategoryCodes = $this->getProduct($identifier)->getCategoryCodes();

        Assert::isEmpty($productCategoryCodes, sprintf(
            "Expected empty category, having '%s'.",
            json_encode($productCategoryCodes)
        ));
    }

    /**
     * @Then the product :identifier should not be in any group
     */
    public function theProductShouldNotBeInAnyGroup(string $identifier): void
    {
        $expectedGroupCodes = $this->getProduct($identifier)->getGroupCodes();

        Assert::isEmpty($expectedGroupCodes, sprintf(
            "Expected empty group, having '%s'.",
            json_encode($expectedGroupCodes)
        ));
    }

    /**
    * @Then the product :identifier should not have any association
    */
    public function theProductShouldNotHaveAnyAssociation(string $identifier): void
    {
        $associationsCount = 0;
        foreach ($this->getProduct($identifier)->getAssociations() as $association) {
            $associationsCount += count($association->getProducts());
            $associationsCount += count($association->getProductModels());
            $associationsCount += count($association->getGroups());
        }

        Assert::same($associationsCount, 0, sprintf(
            "Expected empty association, having '%d' association(s).",
            $associationsCount
        ));
    }

    private function getProduct(string $identifier): ProductInterface
    {
        $product = $this->productRepository->findOneByIdentifier($identifier);
        if (null === $product) {
            throw new \RuntimeException(sprintf('Product with identifier "%s" is not found.', $identifier));
        }

        return $product;
    }

    private function importRules(string $yaml): void
    {
        $normalizedRules = Yaml::parse($yaml);

        foreach ($normalizedRules['rules'] as $code => $normalizedRule) {
            $normalizedRule['code'] = $code;
            $ruleDefinition = $this->ruleDefinitionProcessor->process($normalizedRule);
            $this->ruleDefinitionRepository->save($ruleDefinition);
        }
    }

    private function getRule(string $ruleDefinitionIdentifier): RuleInterface
    {
        $definition = $this->ruleDefinitionRepository->findOneByIdentifier($ruleDefinitionIdentifier);
        if (null === $definition) {
            throw new \RuntimeException(
                sprintf('Rule definition with the "%s" identifier was not found.', $ruleDefinitionIdentifier)
            );
        }

        return $this->builder->build($definition);
    }

    /**
     * @param RuleInterface[] $rules
     * @param mixed[] $subjects
     */
    private function executeRulesOnSubjects(array $rules, array $subjects): void
    {
        /** @var RuleInterface $rule */
        foreach ($rules as $rule) {
            try {
                $this->productsUpdater->update($rule, $subjects);
                $validSubjects = $this->productsValidator->validate($rule, $subjects);

                $products = array_filter($validSubjects, function ($item) {
                    return $item instanceof ProductInterface;
                });
                foreach ($products as $product) {
                    $this->productRepository->save($product);
                }
                $productModels = array_filter($validSubjects, function ($item) {
                    return $item instanceof ProductModelInterface;
                });
                foreach ($productModels as $productModel) {
                    throw new \RuntimeException('Not implemented');
                }
            } catch (\Exception $e) {
                ExceptionContext::addException($e);
            }
        }
    }
}
