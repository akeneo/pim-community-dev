<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypeTranslations;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;
use PhpSpec\ObjectBehavior;

class AssociationTranslatorSpec extends ObjectBehavior
{
    function let(
        AssociationColumnsResolver $associationColumnsResolver,
        LabelTranslatorInterface $labelTranslator,
        GetAssociationTypeTranslations $getAssociationTypeTranslations
    ) {
        $this->beConstructedWith($associationColumnsResolver, $labelTranslator, $getAssociationTypeTranslations);
    }

    function it_supports_associations_and_quantified_associations_columns(AssociationColumnsResolver $associationColumnsResolver)
    {
        $associationColumnsResolver->resolveAssociationColumns()->willReturn([
            'ASSOC_TYPE_1-groups',
            'ASSOC_TYPE_1-products',
            'ASSOC_TYPE_1-product_uuids',
            'ASSOC_TYPE_1-product_models',
            'ASSOC_TYPE_2-groups',
            'ASSOC_TYPE_2-products',
            'ASSOC_TYPE_2-product_uuids',
            'ASSOC_TYPE_2-product_models',
        ]);
        $associationColumnsResolver->resolveQuantifiedAssociationColumns()->willReturn([
            'QUANTIFIED_ASSOC_TYPE_1-products',
            'QUANTIFIED_ASSOC_TYPE_1-products-quantity',
            'QUANTIFIED_ASSOC_TYPE_1-product_models',
            'QUANTIFIED_ASSOC_TYPE_1-product_models-quantity',
            'QUANTIFIED_ASSOC_TYPE_2-products',
            'QUANTIFIED_ASSOC_TYPE_2-products-quantity',
            'QUANTIFIED_ASSOC_TYPE_2-product_models',
            'QUANTIFIED_ASSOC_TYPE_2-product_models-quantity',
        ]);

        $this->supports('ASSOC_TYPE_1-groups')->shouldReturn(true);
        $this->supports('ASSOC_TYPE_1-product_models')->shouldReturn(true);
        $this->supports('ASSOC_TYPE_2-products')->shouldReturn(true);
        $this->supports('ASSOC_TYPE_2-product_uuids')->shouldReturn(true);
        $this->supports('QUANTIFIED_ASSOC_TYPE_1-products')->shouldReturn(true);
        $this->supports('QUANTIFIED_ASSOC_TYPE_1-products-quantity')->shouldReturn(true);
        $this->supports('QUANTIFIED_ASSOC_TYPE_2-product_models')->shouldReturn(true);
        $this->supports('QUANTIFIED_ASSOC_TYPE_2-product_models-quantity')->shouldReturn(true);
        $this->supports('something_else')->shouldReturn(false);
        $this->supports('another_one')->shouldReturn(false);
    }

    function it_translates_associations_and_quantified_associations_columns(
        AssociationColumnsResolver $associationColumnsResolver,
        LabelTranslatorInterface $labelTranslator,
        GetAssociationTypeTranslations $getAssociationTypeTranslations
    ) {
        $associationColumnsResolver->resolveAssociationColumns()->willReturn([
            'ASSOC_TYPE_1-groups',
            'ASSOC_TYPE_2-products',
            'ASSOC_TYPE_2-product_uuids',
            'ASSOC_TYPE_2-product_models',
        ]);
        $associationColumnsResolver->resolveQuantifiedAssociationColumns()->willReturn([
            'QUANTIFIED_ASSOC_TYPE_1-products',
            'QUANTIFIED_ASSOC_TYPE_1-products-quantity',
            'QUANTIFIED_ASSOC_TYPE_1-product_models',
            'QUANTIFIED_ASSOC_TYPE_1-product_models-quantity',
            'QUANTIFIED_ASSOC_TYPE_2-products',
            'QUANTIFIED_ASSOC_TYPE_2-products-quantity',
            'QUANTIFIED_ASSOC_TYPE_2-product_models',
            'QUANTIFIED_ASSOC_TYPE_2-product_models-quantity',
        ]);
        $associationColumnsResolver->resolveQuantifiedIdentifierAssociationColumns()->willReturn([
            'QUANTIFIED_ASSOC_TYPE_1-products',
            'QUANTIFIED_ASSOC_TYPE_1-product_models',
            'QUANTIFIED_ASSOC_TYPE_2-products',
            'QUANTIFIED_ASSOC_TYPE_2-product_models',
        ]);
        $associationColumnsResolver->resolveQuantifiedQuantityAssociationColumns()->willReturn([
            'QUANTIFIED_ASSOC_TYPE_1-products-quantity',
            'QUANTIFIED_ASSOC_TYPE_1-product_models-quantity',
            'QUANTIFIED_ASSOC_TYPE_2-products-quantity',
            'QUANTIFIED_ASSOC_TYPE_2-product_models-quantity',
        ]);

        $labelTranslator->translate('pim_common.groups', 'fr_FR', '[groups]')->willReturn('Groupes');
        $labelTranslator->translate('pim_common.products', 'fr_FR', '[products]')->willReturn('Produits');
        $labelTranslator->translate('pim_common.product_uuids', 'fr_FR', '[product_uuids]')->willReturn('Produits');
        $labelTranslator->translate('pim_common.product_models', 'fr_FR', '[product_models]')->willReturn('Modèles de produits');
        $labelTranslator->translate('pim_common.quantity', 'fr_FR', '[quantity]')->willReturn('Quantité');

        $getAssociationTypeTranslations->byAssociationTypeCodeAndLocale([
            'ASSOC_TYPE_1',
            'ASSOC_TYPE_2',
            'QUANTIFIED_ASSOC_TYPE_1'
        ], 'fr_FR')->willReturn([
            'ASSOC_TYPE_1' => 'premiere association',
            'ASSOC_TYPE_2' => 'deuxieme association',
            'QUANTIFIED_ASSOC_TYPE_1' => 'association quantifiée'
        ]);

        $this->warmup([
            'ASSOC_TYPE_1-groups',
            'ASSOC_TYPE_2-products',
            'ASSOC_TYPE_2-product_uuids',
            'ASSOC_TYPE_2-product_models',
            'QUANTIFIED_ASSOC_TYPE_1-products',
            'QUANTIFIED_ASSOC_TYPE_1-product_models'
        ], 'fr_FR');

        $this->translate('ASSOC_TYPE_1-groups', 'fr_FR')->shouldReturn('premiere association Groupes');
        $this->translate('ASSOC_TYPE_2-products', 'fr_FR')->shouldReturn('deuxieme association Produits');
        $this->translate('ASSOC_TYPE_2-product_uuids', 'fr_FR')->shouldReturn('deuxieme association Produits');
        $this->translate('ASSOC_TYPE_2-product_models', 'fr_FR')->shouldReturn('deuxieme association Modèles de produits');
        $this->translate('QUANTIFIED_ASSOC_TYPE_1-products', 'fr_FR')->shouldReturn('association quantifiée Produits');
        $this->translate('QUANTIFIED_ASSOC_TYPE_1-products-quantity', 'fr_FR')->shouldReturn('association quantifiée Produits Quantité');
        $this->translate('QUANTIFIED_ASSOC_TYPE_1-product_models', 'fr_FR')->shouldReturn('association quantifiée Modèles de produits');
        $this->translate('QUANTIFIED_ASSOC_TYPE_1-product_models-quantity', 'fr_FR')->shouldReturn('association quantifiée Modèles de produits Quantité');
        $this->translate('QUANTIFIED_ASSOC_TYPE_2-products', 'fr_FR')->shouldReturn('[QUANTIFIED_ASSOC_TYPE_2] Produits');
        $this->translate('QUANTIFIED_ASSOC_TYPE_2-products-quantity', 'fr_FR')->shouldReturn('[QUANTIFIED_ASSOC_TYPE_2] Produits Quantité');
        $this->translate('QUANTIFIED_ASSOC_TYPE_2-product_models', 'fr_FR')->shouldReturn('[QUANTIFIED_ASSOC_TYPE_2] Modèles de produits');
        $this->translate('QUANTIFIED_ASSOC_TYPE_2-product_models-quantity', 'fr_FR')->shouldReturn('[QUANTIFIED_ASSOC_TYPE_2] Modèles de produits Quantité');
    }
}
