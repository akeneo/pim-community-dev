<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Rule;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\RuleTemplate;
use Akeneo\ReferenceEntity\Domain\Query\Record\AccessibleRecord;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RuleCompilerSpec extends ObjectBehavior
{
    public function let(DenormalizerInterface $ruleDenormalizer)
    {
        $this->beConstructedWith($ruleDenormalizer, Rule::class);
    }

    public function it_compiles_a_rule_template_with_an_accessible_record(
        DenormalizerInterface $ruleDenormalizer,
        RuleTemplate $ruleTemplate,
        AccessibleRecord $accessibleRecord,
        Rule $rule
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

        $accessibleRecord->hasValue('code')->willReturn(true);
        $accessibleRecord->hasValue('product_sku')->willReturn(true);
        $accessibleRecord->hasValue('target_attribute')->willReturn(true);

        $accessibleRecord->getValue('code')->willReturn('packshot_123');
        $accessibleRecord->getValue('product_sku')->willReturn('product_53');
        $accessibleRecord->getValue('target_attribute')->willReturn('packshot');

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

        $ruleDenormalizer->denormalize([
            'code' => '',
            'priority' => '',
            'conditions' => $expectedConditions,
            'actions' => $expectedActions
        ], Rule::class)->willReturn($rule);

        $this->compile($ruleTemplate, $accessibleRecord);
    }
    
    public function it_replaces_only_fields_and_values_in_the_template(
        DenormalizerInterface $ruleDenormalizer,
        RuleTemplate $ruleTemplate,
        AccessibleRecord $accessibleRecord,
        Rule $rule
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
                'field' => '{{target_attribute}}',
                'value' => '{{code}}'
            ]
        ];

        $ruleTemplate->getConditions()->willReturn($conditions);
        $ruleTemplate->getActions()->willReturn($actions);

        $accessibleRecord->hasValue('code')->willReturn(true);
        $accessibleRecord->hasValue('product_sku')->willReturn(true);
        $accessibleRecord->hasValue('target_attribute')->willReturn(true);

        $accessibleRecord->getValue('code')->willReturn('packshot_123');
        $accessibleRecord->getValue('product_sku')->willReturn('product_53');
        $accessibleRecord->getValue('target_attribute')->willReturn('packshot');

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
            ]
        ];

        $ruleDenormalizer->denormalize([
            'code' => '',
            'priority' => '',
            'conditions' => $expectedConditions,
            'actions' => $expectedActions
        ], Rule::class)->willReturn($rule);

        $this->compile($ruleTemplate, $accessibleRecord);
    }
    
    public function it_does_not_replace_if_accessible_record_does_not_have_the_value(
        DenormalizerInterface $ruleDenormalizer,
        RuleTemplate $ruleTemplate,
        AccessibleRecord $accessibleRecord,
        Rule $rule
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

        $accessibleRecord->hasValue('code')->willReturn(true);
        $accessibleRecord->hasValue('product_sku')->willReturn(true);
        $accessibleRecord->hasValue('target_attribute')->willReturn(false);

        $accessibleRecord->getValue('code')->willReturn('packshot_123');
        $accessibleRecord->getValue('product_sku')->willReturn('product_53');

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

        $ruleDenormalizer->denormalize([
            'code' => '',
            'priority' => '',
            'conditions' => $expectedConditions,
            'actions' => $expectedActions
        ], Rule::class)->willReturn($rule);

        $this->compile($ruleTemplate, $accessibleRecord);
    }
}
