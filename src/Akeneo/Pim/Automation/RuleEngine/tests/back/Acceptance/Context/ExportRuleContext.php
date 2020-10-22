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
use Akeneo\Test\Pim\Automation\RuleEngine\Common\Context\ExceptionContext;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class ExportRuleContext implements Context
{
    private const YAML_RULES_CONFIG = <<<YAML
rules:
  set_name:
    priority: 10
    enabled: true
    conditions:
      - field: sku
        operator: '='
        value: my-loafer
    actions:
      - type: set
        field: name
        value: 'My loafer'
        locale: en_US
    labels:
      en_US: 'Set name'
      fr_FR: 'Change le nom'
  set_another_name:
    enabled: false
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
    labels: []
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
    labels: []
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
    labels: []
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
          - field: color
            label_locale: en_US
          - field: connectivity
          - field: connectivity
            label_locale: fr_FR
          - field: designer
          - field: designers
        to:
          field: description
          locale: en_US
          scope: ecommerce
    labels: []
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
    labels: []
  calculate:
    priority: 20
    labels: []
    conditions:
      - field: family
        operator: IN
        value:
          - camcorders
    actions:
      - type: calculate
        destination:
          field: item_weight
          scope: ecommerce
          locale: en_US
        source:
          field: weight
        operation_list:
          -
            operator: multiply
            value: 10
          -
            operator: subtract
            value: 3              
      - type: calculate
        round_precision: 2
        destination:
          field: rounded_item_weight
        source:
          field: weight
        operation_list:
          -
            operator: divide
            value: 3
YAML;

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
     * @Given I import several rules
     */
    public function importSeveralRules(): void
    {
        $this->importRules(static::YAML_RULES_CONFIG);
    }

    /**
     * @When I export all the rules
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
     * @Then the export data contains all rules
     */
    public function theExportFileContainsAllRules()
    {
        $expectedLines = Yaml::parse(static::YAML_RULES_CONFIG);
        // The YAML writer does this merge before write the line. We do the same thing to compare.
        $normalizedRules = call_user_func_array('array_merge', static::$normalizedRules);

        // Add default values to initial config
        foreach ($expectedLines['rules'] as $code => $rule) {
            if (!array_key_exists('priority', $rule)) {
                $expectedLines['rules'][$code]['priority'] = 0;
            }
            if (!array_key_exists('enabled', $rule)) {
                $expectedLines['rules'][$code]['enabled'] = true;
            }
        }

        Assert::eq($normalizedRules, $expectedLines['rules']);
    }

    private function importRules(string $yaml)
    {
        $normalizedRules = Yaml::parse($yaml);

        foreach ($normalizedRules['rules'] as $code => $normalizedRule) {
            $normalizedRule['code'] = $code;
            $ruleDefinition = $this->ruleDefinitionDenormalizerProcessor->process($normalizedRule);
            Assert::implementsInterface($this->ruleDefinitionRepository, SaverInterface::class);
            $this->ruleDefinitionRepository->save($ruleDefinition);
        }
    }
}
