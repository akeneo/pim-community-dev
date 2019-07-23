<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Box\Spout\Reader\IteratorInterface;
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
                    'items' => ['{{ code }}']
                ]
            ]
        ];
        $this->beConstructedThrough('createFromNormalized', [[$ruleTemplate]]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RuleTemplateCollection::class);
    }

    public function it_should_contain_only_rule_template_objects()
    {
        $ruleTemplate = 'wrong_rule_template';
        $this->beConstructedThrough('createFromNormalized', [[$ruleTemplate]]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_can_be_created_with_no_rule_templates()
    {
        $noPrefix = self::empty();
        $noPrefix->normalize()->shouldReturn([]);
    }

    function it_says_if_it_holds_no_rule_templates()
    {
        $this->isEmpty()->shouldReturn(false);
        self::empty()->isEmpty()->shouldReturn(true);
    }

    function it_can_normalize_itself()
    {
        $normalizedRuleTemplate = [
            'product_selections' => [
                [
                    'field' => 'sku',
                    'operator' => 'EQUALS',
                    'value' => '{{product_sku}}',
                    'channel' => null,
                    'locale' => null
                ]
            ],
            'assign_assets_to' => [
                [
                    'attribute' => '{{target_attribute}}',
                    'mode' => 'add',
                    'channel' => null,
                    'locale' => null
                ]
            ]
        ];
        $this->normalize()->shouldReturn([$normalizedRuleTemplate]);
    }

    function it_can_be_iterable()
    {
        $this->getIterator()
             ->count()
             ->shouldBe(1);
    }
}
