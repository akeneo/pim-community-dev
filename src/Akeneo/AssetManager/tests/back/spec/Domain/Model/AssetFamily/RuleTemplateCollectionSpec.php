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
        $content = [
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
        $ruleTemplate = RuleTemplate::createFromNormalized($content);
        $this->beConstructedThrough('fromArray', [[$ruleTemplate]]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RuleTemplateCollection::class);
    }

    public function it_should_contain_objects()
    {
        $ruleTemplate = 'wrong_rule_template';
        $this->beConstructedThrough('fromArray', [[$ruleTemplate]]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_should_contain_rule_template_objects()
    {
        $content = [
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
        $ruleTemplate = new \stdClass($content);
        $this->beConstructedThrough('fromArray', [[$ruleTemplate]]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
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
