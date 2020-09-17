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

use Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Denormalization\RuleDefinitionProcessor;
use Akeneo\Test\Pim\Automation\RuleEngine\Common\Context\ExceptionContext;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Normalizer\RuleDefinitionNormalizer;
use AkeneoEnterprise\Test\Acceptance\Rule\RuleDefinition\InMemoryRuleDefinitionRepository;
use Behat\Behat\Context\Context;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ImportRuleContext implements Context
{
    /** @var RuleDefinitionProcessor */
    private $ruleDefinitionProcessor;

    /** @var InMemoryRuleDefinitionRepository */
    private $ruleDefinitionRepository;

    /** @var RuleDefinitionNormalizer */
    private $ruleDefinitionNormalizer;

    private $importedRules = [];

    public function __construct(
        RuleDefinitionProcessor $ruleDefinitionProcessor,
        InMemoryRuleDefinitionRepository $ruleDefinitionRepository,
        RuleDefinitionNormalizer $ruleDefinitionNormalizer
    ) {
        $this->ruleDefinitionProcessor = $ruleDefinitionProcessor;
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->ruleDefinitionNormalizer = $ruleDefinitionNormalizer;
    }

    /**
     * @When /^I import a valid concatenate rule$/
     */
    public function importAValidConcatenateRule(): void
    {
        $rulesConfig = <<<YAML
rules:
    concatenate:
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
                  - field: connectivity
                  - field: connectivity
                    label_locale: fr_FR
                  - field: designer
                    label_locale: fr_FR
                  - new_line: ~
                  - text: 'A text'
                  - field: designers
                    label_locale: fr_FR
              to:
                  field: description
                  locale: en_US
                  scope: ecommerce
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When /^I import a valid calculate rule$/
     */
    public function importAValidCalculateRule(): void
    {
        $rulesConfig = <<<YAML
rules:
    calculate:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: calculate
              destination:
                field: weight
              source:
                field: item_weight
                scope: ecommerce
                locale: en_US
              operation_list:
                - operator: multiply
                  value: 1000
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When I import a valid calculate rule with round_precision parameter
     */
    public function importAValidCalculateRuleWithRoundPrecisionParameter(): void
    {
        $rulesConfig = <<<YAML
rules:
    calculate_with_round:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: calculate
              round_precision: 2
              destination:
                field: weight
              source:
                field: item_weight
                scope: ecommerce
                locale: en_US
              operation_list:
                - operator: multiply
                  value: 1000
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When I import a calculate rule with invalid round_precision parameter
     */
    public function importACalculateRuleWithInvalidRoundPrecisionParameter(): void
    {
        $rulesConfig = <<<YAML
rules:
    calculate_with_round:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: calculate
              round_precision: 'foo'
              destination:
                field: weight
              source:
                field: item_weight
                scope: ecommerce
                locale: en_US
              operation_list:
                - operator: multiply
                  value: 1000
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @Then the rule list contains the imported :code rule
     */
    public function theRuleListContainsTheValidRule(string $code)
    {
        $ruleDefinition = $this->ruleDefinitionRepository->findOneByIdentifier($code);
        if (null === $ruleDefinition) {
            throw new \LogicException(sprintf('The "%s" rule was not found.', $code));
        }

        $normalizedRule = $this->ruleDefinitionNormalizer->normalize($ruleDefinition);

        Assert::eq($normalizedRule['priority'], $this->importedRules[$code]['priority'] ?? 0);
        Assert::eq($normalizedRule['content']['conditions'], $this->importedRules[$code]['conditions']);
        Assert::eq($normalizedRule['content']['actions'], $this->importedRules[$code]['actions']);
    }

    /**
     * @When /^I import a concatenate rule with invalid source attributes$/
     */
    public function importAConcatenateRuleWithInvalidSourceAttributes(): void
    {
        $rulesConfig = <<<YAML
rules:
    test1:
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
                  - field: categories
                  - field: name
                    locale: en_US
                  - field: pim_brand
                    text: "akeneo"
                  - field: pim_brand
                    new_line: ~
                  - text: "akeneo"
                    new_line: ~
                  - locale: en_US
              to:
                  field: description
                  locale: en_US
                  scope: ecommerce
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When /^I import a concatenate rule with missing from and to keys$/
     */
    public function importAConcatenateRuleWithMissingFromAndToKeys(): void
    {
        $rulesConfig = <<<YAML
rules:
    test1:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: concatenate              
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When I import a calculate rule with invalid attribute types
     */
    public function importACalculateRuleWithInvalidAttributeTypes(): void
    {
        $rulesConfig = <<<YAML
rules:
    calculate:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: calculate
              destination:
                field: name
                locale: en_US
              source:
                field: description
                locale: en_US
                scope: ecommerce
              operation_list:
                - operator: multiply
                  field: color
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When I import a calculate rule with invalid currencies
     */
    public function importACalculateRuleWithInvalidCurrencies(): void
    {
        $rulesConfig = <<<YAML
rules:
    calculate:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: calculate
              destination:
                field: price
              source:
                field: price
                currency: USD
              operation_list:
                - operator: multiply
                  value: 1.08
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When I import a calculate rule with invalid channels
     */
    public function importACalculateRuleWithInvalidChannels(): void
    {
        $rulesConfig = <<<YAML
rules:
    calculate:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: calculate
              destination:
                field: item_weight
                locale: en_US
                scope: print
              source:
                field: item_weight
                locale: fr_FR
              operation_list:
                - operator: add
                  field: in_stock
                  scope: ecommerce
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When I import a calculate rule with invalid locales
     */
    public function importACalculateRuleWithInvalidLocales(): void
    {
        $rulesConfig = <<<YAML
rules:
    calculate:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: calculate
              destination:
                field: item_weight
                locale: es_ES
                scope: ecommerce
              source:
                field: item_weight
                scope: ecommerce
              operation_list:
                - operator: add
                  field: in_stock
                  locale: en_US
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When I import a calculate rule with an invalid measurement unit in destination
     */
    public function importACalculateRuleWithAnInvalidMeasurementUnit(): void
    {
        $rulesConfig = <<<YAML
rules:
    calculate:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: calculate
              destination:
                field: processor
                unit: GIGAHERTZ                
              source:
                field: item_weight
                locale: en_US
              operation_list:
                - operator: multiply
                  value: 1000
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When /^I import a concatenate rule with unknown target attribute$/
     */
    public function importAConcatenateRuleWithUnknownTargetAttributes(): void
    {
        $rulesConfig = <<<YAML
rules:
    test1:
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
                  field: unknown
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When /^I import a concatenate rule with non text target attribute$/
     */
    public function importAConcatenateRuleWithNonTextTargetAttributes(): void
    {
        $rulesConfig = <<<YAML
rules:
    test1:
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
                  field: sku
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When /^I import a concatenate rule with new line and a text target attribute$/
     */
    public function importAConcatenateRuleWithNewLineAndATextTargetAttribute(): void
    {
        $rulesConfig = <<<YAML
rules:
    test1:
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
                  - new_line: ~
                  - text: 'new product'
                  - new_line: ~
              to:
                  field: name
                  locale: en_US
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When I import a valid clear rule
     */
    public function importAValidClearRule(): void
    {
        $rulesConfig = <<<YAML
rules:
    test_clear:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: clear
              field: name
              locale: en_US
            - type: clear
              field: pim_brand
            - type: clear
              field: processor
              locale: en_US
            - type: clear
              field: price
            - type: clear
              field: color
            - type: clear
              field: release_date
            - type: clear
              field: weight
            - type: clear
              field: sub_description
            - type: clear
              field: description
              locale: en_US
              scope: ecommerce
            - type: clear
              field: connectivity
            - type: clear
              field: categories
            - type: clear
              field: groups
            - type: clear
              field: associations
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @Then the rule list contains the valid clear rule
     */
    public function theRuleListContainsTheClearConcatenateRule()
    {
        $code = 'test_clear';
        $ruleDefinitions = $this->ruleDefinitionRepository->findAll();

        /** @var RuleDefinitionInterface $ruleDefinition */
        foreach ($ruleDefinitions as $ruleDefinition) {
            if ($ruleDefinition->getCode() === $code) {
                $content = $ruleDefinition->getContent();

                Assert::count($content['actions'], 13);
                Assert::eq($content['actions'][0]['type'], 'clear');

                return;
            }
        }

        throw new \LogicException(sprintf('The "%s" rule was not found.', $code));
    }

    /**
     * @When I import a clear rule with unknown attribute
     */
    public function importAClearRuleWithUnknownAttribute(): void
    {
        $rulesConfig = <<<YAML
rules:
    test_clear:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: clear
              field: unknown
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @When I import a clear rule with localized attribute and without locale
     */
    public function importAClearRuleWithLocalizedAttributeAndWithoutLocale(): void
    {
        $rulesConfig = <<<YAML
rules:
    test_clear:
        priority: 90
        conditions:
            - field: family
              operator: IN
              value:
                  - camcorders
        actions:
            - type: clear
              field: name
YAML;
        $this->importRules($rulesConfig);
    }



    private function importRules(string $yaml)
    {
        $normalizedRules = Yaml::parse($yaml);

        foreach ($normalizedRules['rules'] as $code => $normalizedRule) {
            $normalizedRule['code'] = $code;
            try {
                $ruleDefinition = $this->ruleDefinitionProcessor->process($normalizedRule);
                $this->ruleDefinitionRepository->save($ruleDefinition);
                $this->importedRules[$code] = $normalizedRule;
            } catch (\Exception $e) {
                ExceptionContext::addException($e);
            }
        }
    }
}
