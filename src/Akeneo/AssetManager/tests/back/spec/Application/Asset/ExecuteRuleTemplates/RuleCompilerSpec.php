<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates;

use Akeneo\AssetManager\Application\Asset\ExecuteRuleTemplates\CompiledRule;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RuleCompilerSpec extends ObjectBehavior
{
    public function it_compiles_a_rule_template_with_an_accessible_asset(
        RuleTemplate $ruleTemplate,
        PropertyAccessibleAsset $propertyAccessibleAsset
    ) {
        $conditions = [
            [
                'field' => 'sku',
                'operator' => 'EQUALS',
                'value' => '{{product_sku}}',
            ],
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => 'family_{{product_sku}}'
            ]
        ];

        $actions = [
            [
                'type' => 'add',
                'field' => '{{target_attribute}}',
                'value' => '{{ code }}'
            ]
        ];

        $ruleTemplate->getConditions()->willReturn($conditions);
        $ruleTemplate->getActions()->willReturn($actions);

        $propertyAccessibleAsset->hasValue('code')->willReturn(true);
        $propertyAccessibleAsset->hasValue('product_sku')->willReturn(true);
        $propertyAccessibleAsset->hasValue('target_attribute')->willReturn(true);

        $propertyAccessibleAsset->getValue('code')->willReturn('packshot_123');
        $propertyAccessibleAsset->getValue('product_sku')->willReturn('product_53');
        $propertyAccessibleAsset->getValue('target_attribute')->willReturn('packshot');

        $expectedConditions = [
            [
                'field' => 'sku',
                'operator' => 'EQUALS',
                'value' => 'product_53',
            ],
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => 'family_product_53'
            ]
        ];

        $expectedActions = [
            [
                'type' => 'add',
                'field' => 'packshot',
                'value' => 'packshot_123'
            ]
        ];

        $actualRule = $this->compile($ruleTemplate, $propertyAccessibleAsset);

        $actualRule->getConditions()->shouldBe($expectedConditions);
        $actualRule->getActions()->shouldBe($expectedActions);
    }

    public function it_replaces_only_fields_and_values_in_the_template(
        RuleTemplate $ruleTemplate,
        PropertyAccessibleAsset $propertyAccessibleAsset
    ) {
        $conditions = [
            [
                'field' => 'sku',
                'operator' => '{{operator}}',
                'value' => '{{product_sku}}',
            ],
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => 'family_{{product_sku}}'
            ]
        ];

        $actions = [
            [
                'type' => '{{type}}',
                'field' => '{{target_attribute_single_link}}',
                'value' => '{{code}}'
            ],
            [
                'type' => 'add',
                'field' => '{{target_attribute_multiple_link}}',
                'items' => ['{{code}}']
            ]
        ];

        $ruleTemplate->getConditions()->willReturn($conditions);
        $ruleTemplate->getActions()->willReturn($actions);

        $propertyAccessibleAsset->hasValue('code')->willReturn(true);
        $propertyAccessibleAsset->hasValue('product_sku')->willReturn(true);
        $propertyAccessibleAsset->hasValue('target_attribute_single_link')->willReturn(true);
        $propertyAccessibleAsset->hasValue('target_attribute_multiple_link')->willReturn(true);

        $propertyAccessibleAsset->getValue('code')->willReturn('packshot_123');
        $propertyAccessibleAsset->getValue('product_sku')->willReturn('product_53');
        $propertyAccessibleAsset->getValue('target_attribute_single_link')->willReturn('packshot');
        $propertyAccessibleAsset->getValue('target_attribute_multiple_link')->willReturn('front_views');

        $expectedConditions = [
            [
                'field' => 'sku',
                'operator' => '{{operator}}',
                'value' => 'product_53',
            ],
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => 'family_product_53'
            ]
        ];

        $expectedActions = [
            [
                'type' => '{{type}}',
                'field' => 'packshot',
                'value' => 'packshot_123'
            ],
            [
                'type' => 'add',
                'field' => 'front_views',
                'items' => ['packshot_123']
            ],
        ];

        $actualCompiledRule = $this->compile($ruleTemplate, $propertyAccessibleAsset);
        $actualCompiledRule->getConditions()->shouldBeEqualTo($expectedConditions);
        $actualCompiledRule->getActions()->shouldBeEqualTo($expectedActions);
    }

    public function it_does_not_replace_if_accessible_asset_does_not_have_the_value(
        RuleTemplate $ruleTemplate,
        PropertyAccessibleAsset $propertyAccessibleAsset
    ) {
        $conditions = [
            [
                'field' => 'sku',
                'operator' => 'EQUALS',
                'value' => '{{product_sku}}',
            ],
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => 'family_{{product_sku}}'
            ]
        ];

        $actions = [
            [
                'type' => 'add',
                'field' => '{{target_attribute}}',
                'value' => '{{code}}'
            ]
        ];

        $ruleTemplate->getConditions()->willReturn($conditions);
        $ruleTemplate->getActions()->willReturn($actions);

        $propertyAccessibleAsset->hasValue('code')->willReturn(true);
        $propertyAccessibleAsset->hasValue('product_sku')->willReturn(true);
        $propertyAccessibleAsset->hasValue('target_attribute')->willReturn(false);

        $propertyAccessibleAsset->getValue('code')->willReturn('packshot_123');
        $propertyAccessibleAsset->getValue('product_sku')->willReturn('product_53');

        $expectedConditions = [
            [
                'field' => 'sku',
                'operator' => 'EQUALS',
                'value' => 'product_53',
            ],
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => 'family_product_53'
            ]
        ];

        $expectedActions = [
            [
                'type' => 'add',
                'field' => '{{target_attribute}}',
                'value' => 'packshot_123'
            ]
        ];

        $actualCompiledRule = $this->compile($ruleTemplate, $propertyAccessibleAsset);
        $actualCompiledRule->getConditions()->shouldBe($expectedConditions);
        $actualCompiledRule->getActions()->shouldBe($expectedActions);
    }
}
