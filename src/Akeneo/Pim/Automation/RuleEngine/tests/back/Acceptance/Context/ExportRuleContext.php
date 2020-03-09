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
use Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Normalization\RuleDefinitionProcessor as RuleDefinitionNormalizerProcessor;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ExportRuleContext implements Context
{
    private const YAML_RULES_CONFIG = <<<SQL
rules:
  set_name:
    priority: 10
    conditions:
      - field: sku
        operator: '='
        value: my-loafer
    actions:
      - type: set
        field: name
        value: 'My loafer'
        locale: en_US
  set_another_name:
    priority: 20
    conditions:
      - field: sku
        operator: 'STARTS WITH'
        value: my
    actions:
      - type: set
        field: description
        value: 'A stylish white loafer'
        locale: en_US
        scope: mobile
  copy_name_loafer:
    priority: 30
    conditions:
      - field: sku
        operator: '='
        value: my-loafer
      -
        field: sku
        operator: '='
        value: my-loafer
    actions:
      - type: copy
        from_field: name
        to_field: name
        from_locale: en_US
        to_locale: fr_FR
  remove_categories:
    priority: 40
    conditions:
      - field:    enabled
        operator: =
        value:    false
    actions:
      - type:  remove
        field: categories
        items:
          - 2014_collection
        include_children: true
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
        to:
          field: description
          locale: en_US
          scope: ecommerce
SQL;

    private static $normalizedRules = [];

    /** @var RuleDefinitionProcessor */
    private $ruleDefinitionDenormalizerProcessor;

    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var RuleDefinitionNormalizerProcessor */
    private $ruleDefinitionNormalizerProcessor;

    public function __construct(
        RuleDefinitionProcessor $ruleDefinitionDenormalizerProcessor,
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        RuleDefinitionNormalizerProcessor $ruleDefinitionNormalizerProcessor
    ) {
        $this->ruleDefinitionDenormalizerProcessor = $ruleDefinitionDenormalizerProcessor;
        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->ruleDefinitionNormalizerProcessor = $ruleDefinitionNormalizerProcessor;
    }

    /**
     * @Given /^I import several rules$/
     */
    public function importSeveralRules(): void
    {
        $this->importRules(static::YAML_RULES_CONFIG);
    }

    /**
     * @When /^I export all the rules$/
     */
    public function exportAllTheRules()
    {
        static::$normalizedRules = [];
        $rules = $this->ruleDefinitionRepository->findAll();

        foreach ($rules as $rule) {
            try {
                static::$normalizedRules[] = $this->ruleDefinitionNormalizerProcessor->process($rule);
            } catch (\Exception $e) {
                ExceptionContext::addException($e);
            }
        }
    }

    /**
     * @Then /^the export data contains all rules$/
     */
    public function theExportFileContainsAllRules()
    {
        $expectedLines = Yaml::parse(static::YAML_RULES_CONFIG);
        // The YAML writer does this merge before write the line. We do the same thing to compare.
        $normalizedRules = call_user_func_array('array_merge', static::$normalizedRules);

        Assert::eq($normalizedRules, $expectedLines['rules']);
    }

    private function importRules(string $yaml)
    {
        $normalizedRules = Yaml::parse($yaml);

        foreach ($normalizedRules['rules'] as $code => $normalizedRule) {
            $normalizedRule['code'] = $code;
            $ruleDefinition = $this->ruleDefinitionDenormalizerProcessor->process($normalizedRule);
            $this->ruleDefinitionRepository->save($ruleDefinition);
        }
    }
}
