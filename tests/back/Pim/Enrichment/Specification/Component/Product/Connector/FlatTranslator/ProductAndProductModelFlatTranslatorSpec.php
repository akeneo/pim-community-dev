<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\AttributeValuesFlatTranslator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header\FlatHeaderTranslatorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\HeaderRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValueRegistry;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductAndProductModelFlatTranslatorSpec extends ObjectBehavior
{
    function let(
        HeaderRegistry $headerRegistry,
        PropertyValueRegistry $propertyValueRegistry,
        AttributeValuesFlatTranslator $attributeValuesFlatTranslator
    ) {
        $this->beConstructedWith(
            $headerRegistry,
            $propertyValueRegistry,
            $attributeValuesFlatTranslator
        );
    }

    function it_translates_a_product(
        FlatHeaderTranslatorInterface $headerTranslator,
        HeaderRegistry $headerRegistry,
        AttributeValuesFlatTranslator $attributeValuesFlatTranslator
    ) {
        $headerRegistry->warmup(['sku', 'categories', 'description-en_US', 'enabled', 'groups', 'name-fr_FR', 'collection'], 'fr_FR')->shouldBeCalled();
        $headerRegistry->getTranslator(Argument::any())->willReturn($headerTranslator);
        $headerRegistry->getTranslator('sku')->willReturn(null);

        $headerTranslator->translate('sku', 'fr_FR')->shouldNotBeCalled();
        $headerTranslator->translate('categories', 'fr_FR')->willReturn('Catégories');
        $headerTranslator->translate('description-en_US', 'fr_FR')->willReturn('Description (Anglais Américain)');
        $headerTranslator->translate('enabled', 'fr_FR')->willReturn('Activé');
        $headerTranslator->translate('collection', 'fr_FR')->willReturn('Collection');
        $headerTranslator->translate('groups', 'fr_FR')->willReturn('Groupes');
        $headerTranslator->translate('name-fr_FR', 'fr_FR')->willReturn('Nom (Français Français)');

        $attributeValuesFlatTranslator->supports('sku')->willReturn(false);
        $attributeValuesFlatTranslator->supports('categories')->willReturn(false);
        $attributeValuesFlatTranslator->supports('description-en_US')->willReturn(false);
        $attributeValuesFlatTranslator->supports('name-fr_FR')->willReturn(false);
        $attributeValuesFlatTranslator->supports('collection')->willReturn(false);
        $attributeValuesFlatTranslator->supports('enabled')->willReturn(false);
        $attributeValuesFlatTranslator->supports('groups')->willReturn(false);


        $this->translate([
            [
                'sku' => 1151511,
                'categories' => 'master_femme_chaussures_sandales',
                'description-en_US' => 'Ma description',
                'enabled' => 0,
                'groups' => 'group1',
                'name-fr_FR' => 'Sandales dorées Femme'
            ],
            [
                'sku' => 1151512,
                'categories' => 'master_femme_manteaux_manteaux_dhiver',
                'description-en_US' => 'Ma description1',
                'enabled' => 1,
                'groups' => 'group2,group3',
                'name-fr_FR' => 'Jupe imprimée Femme',
                'collection' => 'summer_2016'
            ]
        ], 'fr_FR', 'ecommerce', true)->shouldReturn([
            [
                '[sku]' => 1151511,
                'Catégories' => 'master_femme_chaussures_sandales',
                'Description (Anglais Américain)' => 'Ma description',
                'Activé' => 0,
                'Groupes' => 'group1',
                'Nom (Français Français)' => 'Sandales dorées Femme'
            ],
            [
                '[sku]' => 1151512,
                'Catégories' => 'master_femme_manteaux_manteaux_dhiver',
                'Description (Anglais Américain)' => 'Ma description1',
                'Activé' => 1,
                'Groupes' => 'group2,group3',
                'Nom (Français Français)' => 'Jupe imprimée Femme',
                'Collection' => 'summer_2016'
            ]
        ]);
    }
}
