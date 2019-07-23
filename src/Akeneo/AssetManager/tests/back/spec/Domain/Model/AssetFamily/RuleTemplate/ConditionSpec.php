<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;

use PhpSpec\ObjectBehavior;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ConditionSpec extends ObjectBehavior
{
    public function it_can_be_created_from_normalized()
    {
        $normalizedCondition = [
            'field' => 'sku',
            'operator' => '=',
            'value' => '{{code}}'
        ];
        $this->beConstructedThrough('createFromNormalized', [$normalizedCondition]);

        $this->normalize()->shouldReturn($normalizedCondition + ['channel' => null, 'locale' => null]);
    }

    public function it_can_be_created_from_normalized_with_channel_and_locale()
    {
        $normalizedCondition = [
            'field' => 'sku',
            'operator' => '=',
            'value' => '{{code}}',
            'channel' => 'en_US',
            'locale' => 'ecommerce'
        ];
        $this->beConstructedThrough('createFromNormalized', [$normalizedCondition]);

        $this->normalize()->shouldReturn($normalizedCondition);
    }

    public function it_throw_errors_if_field_is_not_defined()
    {
        $normalizedCondition = [
            'operator' => '=',
            'value' => '{{code}}',
            'channel' => 'en_US',
            'locale' => 'ecommerce'
        ];
        $this->beConstructedThrough('createFromNormalized', [$normalizedCondition]);

        $this->shouldThrow(new \InvalidArgumentException('Expected the key "field" to exist.'))->duringInstantiation();
    }

    public function it_throw_errors_if_operator_is_not_defined()
    {
        $normalizedCondition = [
            'field' => 'sku',
            'value' => '{{code}}',
            'channel' => 'en_US',
            'locale' => 'ecommerce'
        ];
        $this->beConstructedThrough('createFromNormalized', [$normalizedCondition]);

        $this->shouldThrow(new \InvalidArgumentException('Expected the key "operator" to exist.'))->duringInstantiation();
    }

    public function it_throw_errors_if_value_is_not_defined()
    {
        $normalizedCondition = [
            'field' => 'sku',
            'operator' => '=',
            'channel' => 'en_US',
            'locale' => 'ecommerce'
        ];
        $this->beConstructedThrough('createFromNormalized', [$normalizedCondition]);

        $this->shouldThrow(new \InvalidArgumentException('Expected the key "value" to exist.'))->duringInstantiation();
    }

    public function it_can_be_created_from_product_link_rule()
    {
        $productLinkRule = [
            'field' => 'sku',
            'operator' => '=',
            'value' => '{{code}}',
            'channel' => 'en_US',
            'locale' => 'ecommerce'
        ];
        $this->beConstructedThrough('createFromProductLinkRule', [$productLinkRule]);

        $this->normalize()->shouldReturn([
            'field' => 'sku',
            'operator' => '=',
            'value' => '{{code}}',
            'channel' => 'en_US',
            'locale' => 'ecommerce'
        ]);
    }

    public function it_can_compile_itself(PropertyAccessibleAsset $accessibleAsset)
    {
        $productLinkRule = [
            'field' => 'sku',
            'operator' => '=',
            'value' => '{{code}}',
            'channel' => 'en_US',
            'locale' => 'ecommerce'
        ];
        $this->beConstructedThrough('createFromProductLinkRule', [$productLinkRule]);

        $accessibleAsset->hasValue('code')->willReturn(true);
        $accessibleAsset->getValue('code')->willReturn('nice_asset');
        $this->compile($accessibleAsset)->shouldReturn([
            'field' => 'sku',
            'operator' => '=',
            'value' => 'nice_asset',
            'channel' => 'en_US',
            'locale' => 'ecommerce'
        ]);
    }
}
