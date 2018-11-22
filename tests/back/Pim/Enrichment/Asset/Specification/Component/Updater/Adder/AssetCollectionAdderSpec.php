<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Asset\EnrichmentComponent\Updater\Adder;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class AssetCollectionAdderSpec extends ObjectBehavior
{
    function let(EntityWithValuesBuilderInterface $entityWithValuesBuilder)
    {
        $this->beConstructedWith($entityWithValuesBuilder, ['pim_assets_collection']);
    }

    function it_throws_an_exception_if_locale_is_missing_from_the_options(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute
    ) {
        $data = [];
        $options = ['scope' => 'mobile'];

        $this->shouldThrow(MissingOptionsException::class)->during(
            'addAttributeData', [$entityWithValues, $attribute, $data, $options]
        );
    }

    function it_throws_an_exception_if_scope_is_missing_from_the_options(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute
    ) {
        $data = [];
        $options = ['locale' => 'fr_FR'];

        $this->shouldThrow(MissingOptionsException::class)->during(
            'addAttributeData', [$entityWithValues, $attribute, $data, $options]
        );
    }

    function it_throws_an_exception_if_given_data_is_not_an_array(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute
    ) {
        $data = 'asset_code';
        $options = ['scope' => 'mobile', 'locale' => 'en_US'];

        $this->shouldThrow(InvalidPropertyTypeException::class)->during(
            'addAttributeData', [$entityWithValues, $attribute, $data, $options]
        );
    }

    function it_supports_only_assets_collection_attributes(
        AttributeInterface $notAssetCollectionAttribute,
        AttributeInterface $assetCollectionAttribute
    ) {
        $assetCollectionAttribute->getType()->willReturn('pim_assets_collection');
        $notAssetCollectionAttribute->getType()->willReturn('pim_catalog_multiselect');

        $this->supports($assetCollectionAttribute)->shouldReturn(true);
        $this->supports($notAssetCollectionAttribute)->shouldReturn(false);

        $this->supportsAttribute($assetCollectionAttribute)->shouldReturn(true);
        $this->supportsAttribute($notAssetCollectionAttribute)->shouldReturn(false);
    }

    function it_adds_asset_codes_to_an_attribute_on_an_entity_with_values(
        $entityWithValuesBuilder,
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute
    ) {
        $data = ['asset_code_1', 'asset_code_2'];
        $options = ['locale' => '', 'scope' => ''];

        $entityWithValuesBuilder->addOrReplaceValue(
            $entityWithValues,
            $attribute,
            '',
            '',
            $data
        )->shouldBeCalled();

        $this->addAttributeData($entityWithValues, $attribute, $data, $options);
    }
}
