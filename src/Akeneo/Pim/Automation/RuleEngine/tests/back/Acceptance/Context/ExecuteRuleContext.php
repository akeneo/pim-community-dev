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
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\BuilderInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Behat\Behat\Context\Context;
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
     * @When /^I execute the concatenate rule on product "([^"]*)"$/
     */
    public function executeTheConcatenateRuleOnProduct(string $identifier): void
    {
        $product = $this->getProduct($identifier);
        $rule = $this->getRule('concatenate_rule');

        $this->executeRulesOnSubjects([$rule], [$product]);
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
     * @Given /^A rule with complex concatenate action$/
     */
    public function aRuleWithComplexConcatenateAction(): void
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
                  - field: price
                    currency: EUR
                  - field: release_date
                    format: d/m/Y
                  - field: weight
              to:
                  field: description
                  scope: ecommerce
                  locale: en_US
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @Then /^the product "([^"]*)" is successfully updated by the complex concatenate rule$/
     */
    public function theProductIsSuccessfullyUpdatedByComplexConcatenateRule(string $identifier): void
    {
        $product = $this->getProduct($identifier);
        $rule = $this->getRule('concatenate_rule');
        EventSubscriberContext::assertNoSkipExecutionForRuleAndEntity($rule, $product);

        $descriptionValue = $product->getValue('description', 'en_US', 'ecommerce');
        Assert::same($descriptionValue->getData(), 'Crown Bolt 75025 SKU75025 100 MEGAHERTZ 99 EUR 01/01/2015 40');
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
