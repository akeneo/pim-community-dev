<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention;

use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConventionInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NullNamingConvention;
use PhpSpec\ObjectBehavior;

class NamingConventionSpec extends ObjectBehavior
{
    function it_is_a_naming_convention()
    {
        $this->beConstructedThrough('createFromNormalized', [
            ['source' => ['property' => 'code'], 'pattern' => '#(.*)#', 'abort_asset_creation_on_error' => true]
        ]);
        $this->shouldBeAnInstanceOf(NamingConvention::class);
        $this->shouldImplement(NamingConventionInterface::class);
    }

    function it_instantiates_a_null_naming_convention_when_provided_with_empty_arguments()
    {
        $this->beConstructedThrough('createFromNormalized', [[]]);
        $this->shouldImplement(NamingConventionInterface::class);
        $this->shouldBeAnInstanceOf(NullNamingConvention::class);
    }

    function it_cannot_be_constructed_without_a_source()
    {
        $this->beConstructedThrough(
            'createFromNormalized',
            [['pattern' => '#(.*)#', 'abort_asset_creation_on_error' => true]]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_without_a_pattern()
    {
        $this->beConstructedThrough(
            'createFromNormalized',
            [['source' => ['property' => 'code'], 'abort_asset_creation_on_error' => true]]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_without_a_abort_asset_creation_on_error_option()
    {
        $this->beConstructedThrough(
            'createFromNormalized',
            [['source' => ['property' => 'code'], 'pattern' => '#(<?product_ref>\w+)(<?attribute>frontview|sideview)\.\w+#']]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_normalized()
    {
        $this->beConstructedThrough(
            'createFromNormalized',
            [['source' => ['property' => 'code'], 'pattern' => '#(<?attribute>\w+)-(<?sku>w+)\.\w+#', 'abort_asset_creation_on_error' => false]]
        );
        $this->normalize()->shouldReturn(
            [
                'source' => [
                    'property' => 'code',
                    'channel' => null,
                    'locale' => null,
                ],
                'pattern' => '#(<?attribute>\w+)-(<?sku>w+)\.\w+#',
                'abort_asset_creation_on_error' => false,
            ]
        );
    }

    function it_is_never_empty()
    {
        $this->beConstructedThrough('createFromNormalized', [
            ['source' => ['property' => 'code'], 'pattern' => '#(.*)#', 'abort_asset_creation_on_error' => true]
        ]);
        $this->isEmpty()->shouldReturn(false);
    }
}
