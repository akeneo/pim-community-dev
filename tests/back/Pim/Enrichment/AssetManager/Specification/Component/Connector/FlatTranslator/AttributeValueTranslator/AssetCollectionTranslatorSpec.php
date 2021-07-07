<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Connector\FlatTranslator\AttributeValueTranslator;

use Akeneo\AssetManager\Infrastructure\PublicApi\Enrich\FindAssetLabelTranslationInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\Connector\FlatTranslator\AttributeValueTranslator\AssetCollectionTranslator;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetCollectionTranslatorSpec extends ObjectBehavior
{
    function let(FindAssetLabelTranslationInterface $findAssetLabelTranslation)
    {
        $this->beConstructedWith($findAssetLabelTranslation);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssetCollectionTranslator::class);
    }

    function it_only_supports_asset_collection_attributes()
    {
        $this->supports('pim_catalog_asset_collection', 'collection')->shouldReturn(true);
        $this->supports('other_attribute_type', 'is_activated')->shouldReturn(false);
    }

    function it_translates_assets_of_asset_collections_with_their_label(
        FindAssetLabelTranslationInterface $findAssetLabelTranslation
    ) {
        $findAssetLabelTranslation
            ->byFamilyCodeAndAssetCodes(
                'videos',
                ['birthday', 'party', 'cake'],
                'fr_FR'
            )
            ->willReturn(
                [
                    'birthday' => 'Anniversaire',
                    'party' => 'Fête',
                    'cake' => 'Gateau'
                ]
            );

        $this->translate('videos', ['reference_data_name' => 'videos'], ['birthday,party', 'cake'], 'fr_FR')
            ->shouldReturn(['Anniversaire,Fête', 'Gateau']);
    }

    function it_returns_the_asset_code_between_brackets_if_the_asset_does_have_a_label(
        FindAssetLabelTranslationInterface $findAssetLabelTranslation
    ) {
        $findAssetLabelTranslation
            ->byFamilyCodeAndAssetCodes('video', ['anniversaire', 'party', 'cake'], 'fr_FR')
            ->willReturn(['anniversaire' => null, 'party' => null, 'cake' => null]);

        $this->translate('color', ['reference_data_name' => 'video'], ['anniversaire,party', 'cake'], 'fr_FR')
            ->shouldReturn(['[anniversaire],[party]', '[cake]']);
    }

    function it_does_not_translate_if_the_reference_data_name_is_null(FindAssetLabelTranslationInterface $findAssetLabelTranslation)
    {
        $findAssetLabelTranslation->byFamilyCodeAndAssetCodes()->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)
            ->during('translate', [
                'color',
                [], // <= No reference data code
                ['anniversaire,party', 'cake'],
                'fr_FR'
            ]);
    }
}
