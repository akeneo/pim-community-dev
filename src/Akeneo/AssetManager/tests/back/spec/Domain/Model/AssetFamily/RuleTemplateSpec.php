<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RuleTemplateSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(RuleTemplate::class);
    }

    public function it_should_contain_product_selections()
    {
        $content = [
            'assign_assets_to' => [
                [
                    'mode'      => 'add',
                    'attribute' => '{{attribute}}'
                ]
            ]
        ];
        $this->beConstructedThrough('createFromProductLinkRule', [$content]);
        $this->shouldThrow(new \InvalidArgumentException('Expected the key "product_selections" to exist.'))->duringInstantiation();
    }

    public function it_should_contain_at_least_one_product_selection()
    {
        $content = [
            'product_selections' => [],
            'assign_assets_to' => [
                [
                    'mode'      => 'add',
                    'attribute' => '{{attribute}}'
                ]
            ]
        ];
        $this->beConstructedThrough('createFromProductLinkRule', [$content]);
        $this->shouldThrow(new \InvalidArgumentException('A rule template should have at least have one condition'))->duringInstantiation();
    }

    public function it_should_contain_at_least_one_product_action()
    {
        $content = [
            'product_selections' => [
                [
                    'field'      => 'sku',
                    'operator' => '=',
                    'value' => '{{code}}'
                ]
            ],
            'assign_assets_to' => []
        ];
        $this->beConstructedThrough('createFromProductLinkRule', [$content]);
        $this->shouldThrow(new \InvalidArgumentException('A rule template should have at least have one action'))->duringInstantiation();
    }

    public function it_should_contain_assign_assets()
    {
        $content = [
            'product_selections' => [
                [
                    'field'    => 'sku',
                    'operator' => '=',
                    'value'    => '{{product_sku}}'
                ]
            ]
        ];
        $this->beConstructedThrough('createFromProductLinkRule', [$content]);
        $this->shouldThrow(new \InvalidArgumentException('Expected the key "assign_assets_to" to exist.'))->duringInstantiation();
    }

    public function it_should_contain_conditions()
    {
        $content = [
            'actions' => [
                [
                    'type'      => 'add',
                    'field' => '{{attribute}}',
                    'value' => '{{code}}'
                ]
            ],
        ];
        $this->beConstructedThrough('createFromNormalized', [$content]);
        $this->shouldThrow(new \InvalidArgumentException('Expected the key "conditions" to exist.'))->duringInstantiation();
    }

    public function it_should_contain_actions()
    {
        $content = [
            'conditions' => [
                [
                    'field'      => 'sku',
                    'operator' => '=',
                    'value' => '{{code}}'
                ]
            ],
        ];
        $this->beConstructedThrough('createFromNormalized', [$content]);
        $this->shouldThrow(new \InvalidArgumentException('Expected the key "actions" to exist.'))->duringInstantiation();
    }

    public function it_should_be_able_to_compile_itself(PropertyAccessibleAsset $accessibleAsset)
    {
        $content = [
            'conditions' => [
                [
                    'field'      => 'sku',
                    'operator' => '=',
                    'value' => '{{code}}'
                ]
            ],
            'actions' => [
                [
                    'field'      => '{{attribute}}',
                    'type' => 'set',
                    'items' => ['{{code}}']
                ]
            ],
        ];
        $this->beConstructedThrough('createFromNormalized', [$content]);

        $accessibleAsset->hasValue(Argument::any())->willReturn(true);
        $accessibleAsset->getValue('code')->willReturn('nice_asset');
        $accessibleAsset->getValue('attribute')->willReturn('sku');
        $compiledRule = $this->compile($accessibleAsset);

        $compiledRule->getConditions()->shouldReturn([
            [
                'field' => 'sku',
                'operator' => '=',
                'value' => 'nice_asset',
                'channel' => null,
                'locale' => null
            ]
        ]);

        $compiledRule->getActions()->shouldReturn([
            [
                'field' => 'sku',
                'type' => 'set',
                'items' => ['nice_asset'],
                'channel' => null,
                'locale' => null
            ]
        ]);
    }

    public function it_should_be_able_to_normalize_itself()
    {
        $content = [
            'product_selections' => [
                [
                    'field'    => 'sku',
                    'operator' => '=',
                    'value'    => '{{product_sku}}'
                ]
            ],
            'assign_assets_to' => [
                [
                    'mode'      => 'add',
                    'attribute' => '{{attribute}}'
                ]
            ]
        ];
        $this->beConstructedThrough('createFromProductLinkRule', [$content]);
        $this->normalize()->shouldReturn([
            'conditions' => [
                [
                    'field'    => 'sku',
                    'operator' => '=',
                    'value'    => '{{product_sku}}',
                    'channel'  => null,
                    'locale'   => null,
                ],
            ],
            'actions' => [
                [
                    'field' => '{{attribute}}',
                    'type'  => 'add',
                    'items' => ['{{code}}'],
                    'channel' => null,
                    'locale'  => null,
                ],
            ],
        ]);
    }
}
