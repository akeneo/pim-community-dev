<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AssociationTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductLabelsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;
use PhpSpec\ObjectBehavior;

class AssociationTranslatorSpec extends ObjectBehavior
{
    function let(
        AssociationColumnsResolver $associationColumnsResolver,
        GetProductModelLabelsInterface $getProductModelLabels,
        GetProductLabelsInterface $getProductLabels,
        GetGroupTranslations $getGroupTranslations
    ) {
        $this->beConstructedWith($associationColumnsResolver, $getProductModelLabels, $getProductLabels, $getGroupTranslations);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssociationTranslator::class);
    }

    function it_only_supports_associations_property(AssociationColumnsResolver $associationColumnsResolver)
    {
        $associationColumnsResolver->resolveAssociationColumns()->willReturn(
            ['X_SELL-products', 'X_SELL-product_models']
        );
        $associationColumnsResolver->resolveQuantifiedIdentifierAssociationColumns()->willReturn(
            ['X_SELL_quantified-products', 'X_SELL_quantified-product_models']
        );

        $this->supports('X_SELL-products')->shouldReturn(true);
        $this->supports('X_SELL_quantified-product_models')->shouldReturn(true);
        $this->supports('X_SELL_quantified-product_models-quantity')->shouldReturn(false);
        $this->supports('other')->shouldReturn(false);
    }

    function it_translates_product_codes_to_labels(GetProductLabelsInterface $getProductLabels)
    {
        $getProductLabels->byIdentifiersAndLocaleAndScope(
            ['hat-m-red', 'hat-xs-red', 'tshirt-l', 'product-with-no-label'],
            'fr_FR',
            'ecommerce'
        )->willReturn(
            [
                'hat-m-red'             => 'Chapeau rouge (taille M)',
                'hat-xs-red'            => 'Chapeau rouge (taille XS)',
                'tshirt-l'              => 'T-shirt simple (taille L)',
                'product-with-no-label' => null,
            ]
        );
        $this->translate(
            'X_SELL_quantified-products',
            ['hat-m-red,hat-xs-red', 'tshirt-l', 'product-with-no-label'],
            'fr_FR',
            'ecommerce'
        )->shouldReturn(
            [
                'Chapeau rouge (taille M),Chapeau rouge (taille XS)',
                'T-shirt simple (taille L)',
                '[product-with-no-label]'
            ]
        );
    }

    function it_translates_product_model_codes_to_labels(GetProductModelLabelsInterface $getProductModelLabels)
    {
        $getProductModelLabels->byCodesAndLocaleAndScope(
            ['braided-hat-m', 'braided-hat-xs', 'tshirt', 'pm-with-no-label'],
            'fr_FR',
            'ecommerce'
        )->willReturn(
            [
                'braided-hat-m'    => 'Chapeau gris (taille M)',
                'braided-hat-xs'   => 'Chapeau gris (taille XS)',
                'tshirt'           => 'T-shirt simple',
                'pm-with-no-label' => null,
            ]
        );
        $this->translate(
            'X_SELL_quantified-product_models',
            ['braided-hat-m,braided-hat-xs', 'tshirt', 'pm-with-no-label'],
            'fr_FR',
            'ecommerce'
        )->shouldReturn(['Chapeau gris (taille M),Chapeau gris (taille XS)', 'T-shirt simple', '[pm-with-no-label]']);
    }

    function it_translates_group_codes_to_labels(GetGroupTranslations $getGroupTranslations)
    {
        $getGroupTranslations->byGroupCodesAndLocale(
            ['summer', 'winter', 'autumn', 'group-with-no-label'],
            'fr_FR'
        )->willReturn(
            [
                'summer'                => 'Été',
                'winter'                => 'Hivers',
                'autumn'                => 'Automne',
                'product-with-no-label' => null,
            ]
        );
        $this->translate(
            'X_SELL-groups',
            ['summer,winter', 'autumn', 'group-with-no-label'],
            'fr_FR',
            'ecommerce'
        )->shouldReturn(
            [
                'Été,Hivers',
                'Automne',
                '[group-with-no-label]'
            ]
        );
    }

    function it_throws_if_it_is_not_a_supported_association()
    {
        $this->shouldThrow(\LogicException::class)
            ->during('translate', [
                'unsupported',
                [],
                'fr_FR',
                'ecommerce'
            ]);
    }
}
