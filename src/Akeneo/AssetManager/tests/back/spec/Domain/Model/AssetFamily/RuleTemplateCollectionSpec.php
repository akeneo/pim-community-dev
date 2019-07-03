<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use PhpSpec\ObjectBehavior;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleTemplateCollectionSpec extends ObjectBehavior
{
    public function let()
    {
        $ruleTemplate = [
            'conditions' => [
                [
                    'field' => 'sku',
                    'operator' => 'EQUALS',
                    'value' => '{{product_sku}}',
                ]
            ],
            'actions' => [
                [
                    'type' => 'add',
                    'field' => '{{target_attribute}}',
                    'value' => '{{ code }}'
                ]
            ]
        ];
        $this->beConstructedThrough('fromArray', [[$ruleTemplate]]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RuleTemplateCollection::class);
    }

    public function it_should_contain_only_rule_template_objects()
    {
        $ruleTemplate = 'wrong_rule_template';
        $this->beConstructedThrough('fromArray', [[$ruleTemplate]]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_can_be_created_with_no_rule_templates()
    {
        $noPrefix = $this::empty();
        $noPrefix->normalize()->shouldReturn([]);
    }

    function it_says_if_it_holds_no_rule_templates()
    {
        $this->isEmpty()->shouldReturn(false);
        $this::empty()->isEmpty()->shouldReturn(true);
    }

    public function it_can_normalize_itself()
    {
        $normalizedRuleTemplate = [
            'conditions' => [
                [
                    'field' => 'sku',
                    'operator' => 'EQUALS',
                    'value' => '{{product_sku}}',
                ]
            ],
            'actions' => [
                [
                    'type' => 'add',
                    'field' => '{{target_attribute}}',
                    'value' => '{{ code }}'
                ]
            ]
        ];
        $this->normalize()->shouldReturn([$normalizedRuleTemplate]);
    }
}
