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
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
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

    public function __construct(
        RuleDefinitionProcessor $ruleDefinitionProcessor,
        InMemoryRuleDefinitionRepository $ruleDefinitionRepository
    ) {
        $this->ruleDefinitionProcessor = $ruleDefinitionProcessor;
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
    }

    /**
     * @When /^I import a valid concatenate rule$/
     */
    public function importAValidConcatenateRule(): void
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
                  field: description
YAML;
        $this->importRules($rulesConfig);
    }

    /**
     * @Then /^the rule list contains the valid concatenate rule/
     */
    public function theRuleListContainsTheValidConcatenateRule()
    {
        $code = 'test1';
        $ruleDefinitions = $this->ruleDefinitionRepository->findAll();

        /** @var RuleDefinitionInterface $ruleDefinition */
        foreach ($ruleDefinitions as $ruleDefinition) {
            if ($ruleDefinition->getCode() === $code) {
                $content = $ruleDefinition->getContent();

                Assert::count($content['actions'], 1);
                Assert::eq($content['actions'][0]['type'], 'concatenate');

                return;
            }
        }

        throw new \LogicException(sprintf('The "%s" rule was not found.', $code));
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
              to:
                  field: description
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

    private function importRules(string $yaml)
    {
        $normalizedRules = Yaml::parse($yaml);

        foreach ($normalizedRules['rules'] as $code => $normalizedRule) {
            $normalizedRule['code'] = $code;
            try {
                $ruleDefinition = $this->ruleDefinitionProcessor->process($normalizedRule);
                $this->ruleDefinitionRepository->save($ruleDefinition);
            } catch (\Exception $e) {
                ExceptionContext::addException($e);
            }
        }
    }
}
