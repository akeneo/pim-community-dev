<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\Product;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

/**
 * @group ce
 */
class ExportProductsWithLabelsIntegration extends AbstractExportTestCase
{
    private array $uuids = [];

    public function testProductExportWithLabels(): void
    {
        $expectedCsvWithTranslations = <<<CSV
[sku];Catégories;Activé;Famille;Parent;Groupes;Couleur;"Est-ce en vente ?";"Une métrique";"Une métrique (Unité)";"Nom (anglais États-Unis)";"Pack Groupes";"Pack Produits";"Pack Modèles de produits";"Association avec des quantitées Produits";"Association avec des quantitées Produits Quantité";"Association avec des quantitées Modèles de produits";"Association avec des quantitées Modèles de produits Quantité";Taille;"Remplacement Groupes";"Remplacement Produits";"Remplacement Modèles de produits";"Vente incitative Groupes";"Vente incitative Produits";"Vente incitative Modèles de produits";"Vente croisée Groupes";"Vente croisée Produits";"Vente croisée Modèles de produits"
apollon_pink_m;T-shirt;Oui;Vêtements;"Tshirt Appolon";;Bleu,Rose;Oui;12;Kilogramme;;;;;;;;;"Taille M";;;;;;;;;
summer_shirt;Été,T-shirt;Oui;Vêtements;;;Bleu,Rose;Non;12;Kilogramme;;;;;"Tshirt Appolon";12;"Tshirt Appolon";5;"Taille L";;;;;;;;"Tshirt Appolon";"Tshirt Appolon"

CSV;
        $this->assertProductExport(
            $expectedCsvWithTranslations,
            ['header_with_label' => true, 'with_label' => true, 'withHeader' => true, 'file_locale' => 'fr_FR']
        );
    }

    public function testProductExportWithLabelsAndUuid(): void
    {
        $expectedCsvWithTranslations = <<<CSV
"Identifiant unique";[sku];Catégories;Activé;Famille;Parent;Groupes;Couleur;"Est-ce en vente ?";"Une métrique";"Une métrique (Unité)";"Nom (anglais États-Unis)";"Pack Groupes";"Pack Modèles de produits";"Pack Produits";"Association avec des quantitées Produits";"Association avec des quantitées Produits Quantité";"Association avec des quantitées Modèles de produits";"Association avec des quantitées Modèles de produits Quantité";Taille;"Remplacement Groupes";"Remplacement Modèles de produits";"Remplacement Produits";"Vente incitative Groupes";"Vente incitative Modèles de produits";"Vente incitative Produits";"Vente croisée Groupes";"Vente croisée Modèles de produits";"Vente croisée Produits"
{$this->uuids['apollon_pink_m']};apollon_pink_m;T-shirt;Oui;Vêtements;"Tshirt Appolon";;Bleu,Rose;Oui;12;Kilogramme;;;;;;;;;"Taille M";;;;;;;;;
{$this->uuids['summer_shirt']};summer_shirt;Été,T-shirt;Oui;Vêtements;;;Bleu,Rose;Non;12;Kilogramme;;;;;"Tshirt Appolon";12;"Tshirt Appolon";5;"Taille L";;;;;;;;"Tshirt Appolon";"Tshirt Appolon"

CSV;
        $this->assertProductExport(
            $expectedCsvWithTranslations,
            [
                'header_with_label' => true,
                'with_label' => true,
                'withHeader' => true,
                'file_locale' => 'fr_FR',
                'with_uuid' => true,
            ]
        );
    }

    public function testProductExportWithMissingLabelsForTheLocale(): void
    {
        $expectedCsvWithNoTranslations = <<<CSV
[uuid];[sku];[categories];[enabled];[family];[parent];[groups];[color];[is_on_sale];[metric];"[metric] ([unit])";"[name] ([en_US])";"[PACK] [groups]";"[PACK] [product_models]";"[PACK] [product_uuids]";"[QUANTITY] [products]";"[QUANTITY] [products] [quantity]";"[QUANTITY] [product_models]";"[QUANTITY] [product_models] [quantity]";[size];"[SUBSTITUTION] [groups]";"[SUBSTITUTION] [product_models]";"[SUBSTITUTION] [product_uuids]";"[UPSELL] [groups]";"[UPSELL] [product_models]";"[UPSELL] [product_uuids]";"[X_SELL] [groups]";"[X_SELL] [product_models]";"[X_SELL] [product_uuids]"
{$this->uuids['apollon_pink_m']};apollon_pink_m;[tshirt];[yes];[clothing];[apollon];;[blue],[pink];[yes];12;[KILOGRAM];;;;;;;;;[m];;;;;;;;;
{$this->uuids['summer_shirt']};summer_shirt;[summer],[tshirt];[yes];[clothing];;;[blue],[pink];[no];12;[KILOGRAM];;;;;[apollon_pink_m];12;[apollon];5;[l];;;;;;;;[apollon];[{$this->uuids['apollon_pink_m']}]

CSV;
        $this->assertProductExport(
            $expectedCsvWithNoTranslations,
            [
                'header_with_label' => true,
                'with_label' => true,
                'withHeader' => true,
                'file_locale' => 'unknown_locale',
                'with_uuid' => true,
            ]
        );
    }

    public function testExportProductsWithDuplicatedHeaders(): void
    {
        $this->loadAdditionalFixtures();;

        $expectedCsvWithNoDuplicatesForHeaders = <<<CSV
[sku];Catégories;Activé;Famille;Parent;Groupes;"Couleur - color";"Est-ce en vente ?";"Couleur - main_color";"Une métrique";"Une métrique (Unité)";"Nom (anglais États-Unis)";"Pack Groupes";"Pack Produits";"Pack Modèles de produits";"Association avec des quantitées Produits";"Association avec des quantitées Produits Quantité";"Association avec des quantitées Modèles de produits";"Association avec des quantitées Modèles de produits Quantité";Taille;"Remplacement Groupes";"Remplacement Produits";"Remplacement Modèles de produits";"Vente incitative Groupes";"Vente incitative Produits";"Vente incitative Modèles de produits";"Vente croisée Groupes";"Vente croisée Produits";"Vente croisée Modèles de produits"
apollon_pink_m;T-shirt;Oui;Vêtements;"Tshirt Appolon";;Bleu,Rose;Oui;;12;Kilogramme;;;;;;;;;"Taille M";;;;;;;;;
stansmith;;Oui;Sneakers;;;;;Blanc;;;;;;;;;;;;;;;;;;;;
summer_shirt;Été,T-shirt;Oui;Vêtements;;;Bleu,Rose;Non;;12;Kilogramme;;;;;"Tshirt Appolon";12;"Tshirt Appolon";5;"Taille L";;;;;;;;"Tshirt Appolon";"Tshirt Appolon"

CSV;

        $this->assertProductExport(
            $expectedCsvWithNoDuplicatesForHeaders,
            [
                'header_with_label' => true,
                'with_label' => true,
                'withHeader' => true,
                'file_locale' => 'fr_FR',
                'with_uuid' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        // Attributes
        $this->createAttribute([
            'code'        => 'is_on_sale',
            'type'        => 'pim_catalog_boolean',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
            'labels' => [
                'fr_FR' => 'Est-ce en vente ?'
            ]
        ]);
        $this->createAttribute([
            'code'        => 'color',
            'type'        => 'pim_catalog_multiselect',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
            'labels' => [
                'fr_FR' => 'Couleur'
            ]
        ]);
        $this->createAttributeOption([
            'code'        => 'pink',
            'attribute'   => 'color',
            'labels' => [
                'fr_FR' => 'Rose'
            ]
        ]);
        $this->createAttributeOption([
            'code'        => 'blue',
            'attribute'   => 'color',
            'labels' => [
                'fr_FR' => 'Bleu'
            ]
        ]);
        $this->createAttribute([
            'code'        => 'size',
            'type'        => 'pim_catalog_simpleselect',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
            'labels' => [
                'fr_FR' => 'Taille'
            ]
        ]);
        $this->createAttributeOption([
            'code'        => 'm',
            'attribute'   => 'size',
            'labels' => [
                'fr_FR' => 'Taille M'
            ]
        ]);
        $this->createAttributeOption([
            'code'        => 'l',
            'attribute'   => 'size',
            'labels' => [
                'fr_FR' => 'Taille L'
            ]
        ]);
        $this->createAttribute([
            'code'        => 'metric',
            'type'        => 'pim_catalog_metric',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
            'decimals_allowed' => false,
            'metric_family' => 'Weight',
            'default_metric_unit' => 'KILOGRAM',
            'negative_allowed' => false,
            'labels' => [
                'fr_FR' => 'Une métrique'
            ]
        ]);
        $this->createAttribute([
            'code'        => 'name',
            'type'        => 'pim_catalog_text',
            'group'       => 'attributeGroupA',
            'localizable' => true,
            'scopable'    => false,
            'labels' => [
                'fr_FR' => 'Nom'
            ]
        ]);

        // Families
        $this->createFamily([
            'code'        => 'clothing',
            'attributes'  => ['sku', 'color', 'size', 'is_on_sale', 'metric', 'name'],
            'attribute_as_label' => 'name',
            'labels' => [
                'fr_FR' => 'Vêtements'
            ]
        ]);
        $this->createFamilyVariant([
            'code'        => 'clothing_color_size',
            'family'      => 'clothing',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['size'],
                    'attributes' => ['color', 'size', 'sku'],
                ],
            ],
            'labels' => [
                'fr_FR' => 'Vêtements par couleurs et tailles'
            ]
        ]);

        // Categories
        $this->createCategory(['code' => 'tshirt', 'labels' => ['fr_FR' => 'T-shirt']]);
        $this->createCategory(['code' => 'summer', 'parent' => 'tshirt', 'labels' => ['fr_FR' => 'Été']]);

        // Associations
        $this->createAssociationType(
            [
                'code' => 'QUANTITY',
                'is_quantified'=> true,
                'labels' => [
                    'fr_FR' => 'Association avec des quantitées'
                ]
            ]
        );


        // Products
        $this->createProductModel(
            [
                'code' => 'apollon',
                'family_variant' => 'clothing_color_size',
                'categories' => ['tshirt'],
                'values'  => [
                    'name' => [['data' => 'Tshirt Appolon', 'locale' => 'fr_FR', 'scope' => null]],
                    'is_on_sale' => [['data' => true, 'locale' => null, 'scope' => null]],
                    'metric'  => [['data' => ['amount' => 12, 'unit' => 'KILOGRAM'], 'locale' => null, 'scope' => null]],
                ]
            ]
        );
        $this->uuids['apollon_pink_m'] = $this->createProduct(
            'apollon_pink_m',
            [
                new SetFamily('clothing'),
                new ChangeParent('apollon'),
                new SetCategories([]),
                new SetSimpleSelectValue('size', null, null, 'm'),
                new SetMultiSelectValue('color', null, null, ['pink','blue'])
            ]
        )->getUuid()->toString();

        $this->uuids['summer_shirt'] = $this->createProduct(
            'summer_shirt',
            [
                new SetFamily('clothing'),
                new SetCategories(['tshirt', 'summer']),
                new SetTextValue('name', null, 'fr_FR', 'summer_shirt_2020'),
                new SetMultiReferenceEntityValue('color', null, null, ['pink', 'blue']),
                new SetSimpleSelectValue('size', null, null, 'l'),
                new SetBooleanValue('is_on_sale', null, null, false),
                new SetMeasurementValue('metric', null, null, 12, 'KILOGRAM'),
                new AssociateProducts('X_SELL', ['apollon_pink_m']),
                new AssociateProductModels('X_SELL', ['apollon']),
                new AssociateQuantifiedProducts('QUANTITY', [
                    new QuantifiedEntity('apollon_pink_m', 12)]
                ),
                new AssociateQuantifiedProductModels('QUANTITY', [
                    new QuantifiedEntity('apollon', 5)
                ])
            ]
        )->getUuid()->toString();

        $frenchCatalogue = $this->get('translator')->getCatalogue('fr_FR');
        $frenchCatalogue->set('pim_common.uuid', 'Identifiant unique');
        $frenchCatalogue->set('pim_common.categories', 'Catégories');
        $frenchCatalogue->set('pim_common.family', 'Famille');
        $frenchCatalogue->set('pim_common.parent', 'Parent');
        $frenchCatalogue->set('pim_common.enabled', 'Activé');
        $frenchCatalogue->set('pim_common.groups', 'Groupes');
        $frenchCatalogue->set('pim_common.products', 'Produits');
        $frenchCatalogue->set('pim_common.product_uuids', 'Produits');
        $frenchCatalogue->set('pim_common.product_models', 'Modèles de produits');
    }

    private function loadAdditionalFixtures(): void
    {
        $this->createAttribute([
            'code'        => 'main_color',
            'type'        => 'pim_catalog_simpleselect',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
            'labels' => [
                'fr_FR' => 'Couleur'
            ]
        ]);
        $this->createAttributeOption([
            'code'        => 'white',
            'attribute'   => 'main_color',
            'labels' => [
                'fr_FR' => 'Blanc',
            ],
        ]);
        $this->createFamily([
            'code'        => 'sneakers',
            'attributes'  => ['sku', 'main_color', 'is_on_sale'],
            'attribute_as_label' => 'sku',
            'labels' => [
                'fr_FR' => 'Sneakers',
            ],
        ]);
        $this->uuids['stansmith'] = $this->createProduct(
            'stansmith',
            [
                new SetFamily('sneakers'),
                new SetIdentifierValue('sku', 'stansmith'),
                new SetSimpleSelectValue('main_color', null, null, 'white'),
            ]
        );
    }
}
