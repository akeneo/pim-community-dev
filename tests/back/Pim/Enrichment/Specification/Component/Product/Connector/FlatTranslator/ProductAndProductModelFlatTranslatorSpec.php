<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValuesFlatTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header\FlatHeaderTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\HeaderRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\ProductAndProductModelFlatTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AssociationTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValueRegistry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductAndProductModelFlatTranslatorSpec extends ObjectBehavior
{
    function let(
        HeaderRegistry $headerRegistry,
        PropertyValueRegistry $propertyValueRegistry,
        AttributeValuesFlatTranslator $attributeValuesFlatTranslator,
        AssociationTranslator $associationTranslator
    ) {
        $this->beConstructedWith(
            $headerRegistry,
            $propertyValueRegistry,
            $attributeValuesFlatTranslator,
            $associationTranslator
        );
    }

    function it_is_initializable() {
        $this->shouldHaveType(ProductAndProductModelFlatTranslator::class);
    }

    function it_translates_a_product(
        FlatHeaderTranslatorInterface $headerTranslator,
        HeaderRegistry $headerRegistry,
        AttributeValuesFlatTranslator $attributeValuesFlatTranslator,
        AssociationTranslator $associationTranslator
    ) {
        $headerRegistry->warmup(['sku', 'categories', 'description-en_US', 'enabled', 'groups', 'name-fr_FR', 'UP_SELL-product_models', 'UP_SELL-products', 'collection'], 'fr_FR')->shouldBeCalled();
        $headerRegistry->getTranslator(Argument::any())->willReturn($headerTranslator);
        $headerRegistry->getTranslator('sku')->willReturn(null);

        $headerTranslator->translate('sku', 'fr_FR')->shouldNotBeCalled();
        $headerTranslator->translate('categories', 'fr_FR')->willReturn('Catégories');
        $headerTranslator->translate('description-en_US', 'fr_FR')->willReturn('Description (Anglais Américain)');
        $headerTranslator->translate('enabled', 'fr_FR')->willReturn('Activé');
        $headerTranslator->translate('collection', 'fr_FR')->willReturn('Collection');
        $headerTranslator->translate('groups', 'fr_FR')->willReturn('Groupes');
        $headerTranslator->translate('name-fr_FR', 'fr_FR')->willReturn('Nom (Français Français)');
        $headerTranslator->translate('UP_SELL-products', 'fr_FR')->willReturn('Proposition achat croisés de produits');
        $headerTranslator->translate('UP_SELL-product_models', 'fr_FR')->willReturn('Proposition achat croisés de modèles');

        $associationTranslator->supports('UP_SELL-products')->willReturn(true);
        $associationTranslator->supports('UP_SELL-product_models')->willReturn(true);

        $associationTranslator->translate('UP_SELL-products', ['scarf,watch', 'scarf,watch'], 'fr_FR', 'ecommerce')
            ->willReturn(['Belle écharpe,Belle montre', 'Belle écharpe,Belle montre']);
        $associationTranslator->translate('UP_SELL-product_models', ['tshirt-xs,hat', 'tshirt-xs,hat'], 'fr_FR', 'ecommerce')
            ->willReturn(['Beau T-shirt,Beau chapeau', 'Beau T-shirt,Beau chapeau']);

        $attributeValuesFlatTranslator->supports('sku')->willReturn(false);
        $attributeValuesFlatTranslator->supports('categories')->willReturn(false);
        $attributeValuesFlatTranslator->supports('description-en_US')->willReturn(false);
        $attributeValuesFlatTranslator->supports('name-fr_FR')->willReturn(false);
        $attributeValuesFlatTranslator->supports('collection')->willReturn(false);
        $attributeValuesFlatTranslator->supports('enabled')->willReturn(false);
        $attributeValuesFlatTranslator->supports('groups')->willReturn(false);
        $attributeValuesFlatTranslator->supports('UP_SELL-products')->willReturn(false);
        $attributeValuesFlatTranslator->supports('UP_SELL-product_models')->willReturn(false);
        $associationTranslator->supports('sku')->willReturn(false);
        $associationTranslator->supports('categories')->willReturn(false);
        $associationTranslator->supports('description-en_US')->willReturn(false);
        $associationTranslator->supports('name-fr_FR')->willReturn(false);
        $associationTranslator->supports('collection')->willReturn(false);
        $associationTranslator->supports('enabled')->willReturn(false);
        $associationTranslator->supports('groups')->willReturn(false);

        $this->translate([
            [
                'sku'                    => 1151511,
                'categories'             => 'master_femme_chaussures_sandales',
                'description-en_US'      => 'Ma description',
                'enabled'                => 0,
                'groups'                 => 'group1',
                'name-fr_FR'             => 'Sandales dorées Femme',
                'UP_SELL-product_models' => 'tshirt-xs,hat',
                'UP_SELL-products'       => 'scarf,watch'
            ],
            [
                'sku'                    => 1151512,
                'categories'             => 'master_femme_manteaux_manteaux_dhiver',
                'description-en_US'      => 'Ma description1',
                'enabled'                => 1,
                'groups'                 => 'group2,group3',
                'name-fr_FR'             => 'Jupe imprimée Femme',
                'collection'             => 'summer_2016',
                'UP_SELL-product_models' => 'tshirt-xs,hat',
                'UP_SELL-products'       => 'scarf,watch'
            ]
        ], 'fr_FR', 'ecommerce', true)
            ->shouldReturn(
                [
                    [
                        'sku--[sku]'                                 => 1151511,
                        'categories--Catégories'                            => 'master_femme_chaussures_sandales',
                        'description-en_US--Description (Anglais Américain)'       => 'Ma description',
                        'enabled--Activé'                                => 0,
                        'groups--Groupes'                               => 'group1',
                        'name-fr_FR--Nom (Français Français)'               => 'Sandales dorées Femme',
                        'UP_SELL-product_models--Proposition achat croisés de modèles'  => 'Beau T-shirt,Beau chapeau',
                        'UP_SELL-products--Proposition achat croisés de produits' => 'Belle écharpe,Belle montre'
                    ],
                    [
                        'sku--[sku]'                                 => 1151512,
                        'categories--Catégories'                            => 'master_femme_manteaux_manteaux_dhiver',
                        'description-en_US--Description (Anglais Américain)'       => 'Ma description1',
                        'enabled--Activé'                                => 1,
                        'groups--Groupes'                               => 'group2,group3',
                        'name-fr_FR--Nom (Français Français)'               => 'Jupe imprimée Femme',
                        'UP_SELL-product_models--Proposition achat croisés de modèles'  => 'Beau T-shirt,Beau chapeau',
                        'UP_SELL-products--Proposition achat croisés de produits' => 'Belle écharpe,Belle montre',
                        'collection--Collection'                            => 'summer_2016'
                    ]
                ]
            );
    }
}
