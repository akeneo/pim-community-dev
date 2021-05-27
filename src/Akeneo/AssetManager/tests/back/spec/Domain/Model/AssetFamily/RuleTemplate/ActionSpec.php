<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;

use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate\Action;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;


/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ActionSpec extends ObjectBehavior
{
    public function let()
    {
        $action = [
            'type'  => 'add',
            'field' => '{{attribute}}',
            'items' => ['{{code}}']
        ];
        $this->beConstructedThrough('createFromNormalized', [$action]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Action::class);
    }

    public function it_could_create_from_product_link_rule()
    {
        $action = [
            'mode'  => 'add',
            'attribute' => '{{attribute}}'
        ];
        $this->beConstructedThrough('createFromProductLinkRule', [$action]);
        $this->shouldHaveType(Action::class);
    }

    public function it_could_not_create_from_product_link_rule_when_the_mode_is_not_allowed()
    {
        $action = [
            'mode'  => 'wrong_mode',
            'attribute' => '{{attribute}}'
        ];
        $this->beConstructedThrough('createFromProductLinkRule', [$action]);
        $this->shouldThrow(new \InvalidArgumentException('The action mode allowed should be one of these : "add, replace"'))->duringInstantiation();
    }

    public function it_could_not_create_an_action_without_field()
    {
        $action = [
            'type'  => 'add',
            'items' => ['{{code}}']
        ];
        $this->beConstructedThrough('createFromNormalized', [$action]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_could_not_create_an_action_without_type()
    {
        $action = [
            'field' => '{{attribute}}',
            'items' => ['{{code}}']
        ];
        $this->beConstructedThrough('createFromNormalized', [$action]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_could_not_create_an_action_without_items()
    {
        $action = [
            'field' => '{{attribute}}',
            'type'  => 'add'
        ];
        $this->beConstructedThrough('createFromNormalized', [$action]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_could_compile_itself(PropertyAccessibleAsset $accessibleAsset)
    {
        $accessibleAsset->hasValue(Argument::any())->willReturn(true);
        $accessibleAsset->getValue('code')->willReturn('nice_asset');
        $accessibleAsset->getValue('attribute')->willReturn('sku');
        $this->compile($accessibleAsset);
        $this->shouldHaveType(Action::class);
    }

    public function it_compile_channel_and_locale(PropertyAccessibleAsset $accessibleAsset)
    {
        $accessibleAsset->hasValue(Argument::any())->willReturn(true);
        $accessibleAsset->getValue('locale')->willReturn('fr_FR');
        $accessibleAsset->getValue('channel')->willReturn('ecommerce');
        $accessibleAsset->getValue('code')->willReturn('nice_asset');
        $accessibleAsset->getValue('attribute')->willReturn('sku');

        $action = [
            'type'  => 'add',
            'field' => '{{attribute}}',
            'items' => ['{{code}}'],
            'locale' => '{{locale}}',
            'channel' => '{{channel}}',
        ];

        $this->beConstructedThrough('createFromNormalized', [$action]);

        $compiledAction = $this->compile($accessibleAsset);
        $compiledAction->normalize()->shouldReturn([
            'field' => 'sku',
            'type' => 'add',
            'items' => ['nice_asset'],
            'channel' => 'ecommerce',
            'locale' => 'fr_FR',
        ]);
    }

    public function it_could_normalize_itself()
    {
        $this->normalize()->shouldReturn(
            [
                'field' => '{{attribute}}',
                'type'  => 'add',
                'items' => ['{{code}}'],
                'channel' => null,
                'locale' => null
            ]
        );
    }
}
