<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Export\ProductModel;

use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;

class ExportProductModelsWithLabelsIntegration extends AbstractExportTestCase
{
    public function testProductModelExportWithLabels()
    {
        $expectedCsvWithTranslations = <<<CSV
[code];"Variante de famille";Parent;Catégories;Couleurs;"Description (anglais États-Unis) - customer_description-en_US";"Couleur principale";Nom;"Pack Groupes";"Pack Produits";"Pack Modèles de produits";"Prix (Ecommerce) (euro)";"Prix (Ecommerce) (dollar des États-Unis)";"Association avec des quantitées Produits";"Association avec des quantitées Produits Quantité";"Association avec des quantitées Modèles de produits";"Association avec des quantitées Modèles de produits Quantité";"Date de sortie (Ecommerce)";"Description (anglais États-Unis) - seller_description-en_US";"Remplacement Groupes";"Remplacement Produits";"Remplacement Modèles de produits";"Vente incitative Groupes";"Vente incitative Produits";"Vente incitative Modèles de produits";"Nom de la variation";"Vente croisée Groupes";"Vente croisée Produits";"Vente croisée Modèles de produits"
apollon;"Vêtement par couleur et par taille";;T-shirts;;"The beautiful Apollon";;apollon;;;;12.30;12.60;;;;;2011-08-21;"Apollon is the most beautiful tshirt, buy it";;;;;;;;;;
apollon_blue;"Vêtement par couleur et par taille";apollon;"Été,T-shirts,Col en v";Bleu,Rose;"The beautiful Apollon";Bleu;apollon;;;;12.30;12.60;;;;;2011-08-21;"Apollon is the most beautiful tshirt, buy it";;;;;;;"my blue tshirt";;;
apollon_pink;"Vêtement par couleur et par taille";apollon;"Col rond,T-shirts";Bleu,Rose;"The beautiful Apollon";Rose;apollon;;;;12.30;12.60;[a_product];12;apollon;5;2011-08-21;"Apollon is the most beautiful tshirt, buy it";;;;;;;"my pink tshirt";;[a_product];apollon

CSV;
        $this->assertProductModelExport(
            $expectedCsvWithTranslations,
            ['header_with_label' => true, 'with_label' => true, 'withHeader' => true, 'file_locale' => 'fr_FR']
        );
    }

    public function testProductModelExportWithMissingLabelsForTheLocale()
    {
        $expectedCsvWithNoTranslations = <<<CSV
[code];[family_variant];[parent];[categories];[colors];"[customer_description] ([en_US])";[main_color];[name];"[PACK] [groups]";"[PACK] [products]";"[PACK] [product_models]";"[price] ([ecommerce]) ([EUR])";"[price] ([ecommerce]) ([USD])";"[QUANTITY] [products]";"[QUANTITY] [products] [quantity]";"[QUANTITY] [product_models]";"[QUANTITY] [product_models] [quantity]";"[release_date] ([ecommerce])";"[seller_description] ([en_US])";"[SUBSTITUTION] [groups]";"[SUBSTITUTION] [products]";"[SUBSTITUTION] [product_models]";"[UPSELL] [groups]";"[UPSELL] [products]";"[UPSELL] [product_models]";[variation_name];"[X_SELL] [groups]";"[X_SELL] [products]";"[X_SELL] [product_models]"
apollon;[clothing_color_size];;[tshirt];;"The beautiful Apollon";;apollon;;;;12.30;12.60;;;;;2011-08-21;"Apollon is the most beautiful tshirt, buy it";;;;;;;;;;
apollon_blue;[clothing_color_size];apollon;[summer],[tshirt],[v-neck];[blue],[pink];"The beautiful Apollon";[blue];apollon;;;;12.30;12.60;;;;;2011-08-21;"Apollon is the most beautiful tshirt, buy it";;;;;;;"my blue tshirt";;;
apollon_pink;[clothing_color_size];apollon;[round-neck],[tshirt];[blue],[pink];"The beautiful Apollon";[pink];apollon;;;;12.30;12.60;[a_product];12;apollon;5;2011-08-21;"Apollon is the most beautiful tshirt, buy it";;;;;;;"my pink tshirt";;[a_product];apollon

CSV;
        $this->assertProductModelExport(
            $expectedCsvWithNoTranslations,
            ['header_with_label' => true, 'with_label' => true, 'withHeader' => true, 'file_locale' => 'unknown_locale']
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function loadFixtures() : void
    {
        $this->createAttribute([
            'code'        => 'name',
            'type'        => 'pim_catalog_text',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
            'labels' => [
                'fr_FR' => 'Nom'
            ]
        ]);

        $this->createAttribute([
            'code'        => 'variation_name',
            'type'        => 'pim_catalog_text',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
            'labels' => [
                'fr_FR' => 'Nom de la variation'
            ]
        ]);

        $this->createAttribute([
            'code'        => 'seller_description',
            'type'        => 'pim_catalog_textarea',
            'group'       => 'attributeGroupA',
            'localizable' => true,
            'scopable'    => false,
            'labels' => [
                'fr_FR' => 'Description'
            ]
        ]);
        $this->createAttribute([
            'code'        => 'customer_description',
            'type'        => 'pim_catalog_textarea',
            'group'       => 'attributeGroupA',
            'localizable' => true,
            'scopable'    => false,
            'labels' => [
                'fr_FR' => 'Description'
            ]
        ]);

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
            'code'        => 'main_color',
            'type'        => 'pim_catalog_simpleselect',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
            'labels' => [
                'fr_FR' => 'Couleur principale'
            ]
        ]);
        $this->createAttribute([
            'code'        => 'colors',
            'type'        => 'pim_catalog_multiselect',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
            'labels' => [
                'fr_FR' => 'Couleurs'
            ]
        ]);
        $this->createAttributeOption([
            'code'        => 'pink',
            'attribute'   => 'main_color',
            'labels' => [
                'fr_FR' => 'Rose'
            ]
        ]);
        $this->createAttributeOption([
            'code'        => 'blue',
            'attribute'   => 'main_color',
            'labels' => [
                'fr_FR' => 'Bleu'
            ]
        ]);
        $this->createAttributeOption([
            'code'        => 'pink',
            'attribute'   => 'colors',
            'labels' => [
                'fr_FR' => 'Rose'
            ]
        ]);
        $this->createAttributeOption([
            'code'        => 'blue',
            'attribute'   => 'colors',
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
            'code'        => 'price',
            'type'        => 'pim_catalog_price_collection',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'decimals_allowed' => true,
            'scopable'    => true,
            'labels' => [
                'fr_FR' => 'Prix'
            ]
        ]);

        $this->createAttribute([
            'code'        => 'release_date',
            'type'        => 'pim_catalog_date',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => true,
            'labels' => [
                'fr_FR' => 'Date de sortie'
            ]
        ]);

        $this->createFamily([
            'code'        => 'clothing',
            'attributes'  => ['sku', 'name', 'variation_name', 'size', 'main_color', 'colors', 'seller_description', 'customer_description', 'price', 'release_date'],
            'attribute_requirements' => [
                'tablet' => ['sku', 'name']
            ],
            'attribute_as_label' => 'name'
        ]);

        $this->createFamilyVariant([
            'code'        => 'clothing_color_size',
            'family'      => 'clothing',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['main_color'],
                    'attributes' => ['main_color', 'colors', 'variation_name'],
                ],
                [
                    'level' => 2,
                    'axes' => ['size'],
                    'attributes' => ['size', 'sku'],
                ],
            ],
            'labels' => [
                'fr_FR' => 'Vêtement par couleur et par taille'
            ]
        ]);

        $this->createCategory(['code' => 'tshirt', 'labels' => ['fr_FR' => 'T-shirts']]);
        $this->createCategory(['code' => 'summer', 'parent' => 'tshirt', 'labels' => ['fr_FR' => 'Été']]);
        $this->createCategory(['code' => 'spring', 'parent' => 'tshirt', 'labels' => ['fr_FR' => 'Printemps']]);
        $this->createCategory(['code' => 'long-sleeves', 'parent' => 'tshirt', 'labels' => ['fr_FR' => 'Manches longues']]);
        $this->createCategory(['code' => 'v-neck', 'parent' => 'long-sleeves', 'labels' => ['fr_FR' => 'Col en v']]);
        $this->createCategory(['code' => 'round-neck', 'parent' => 'long-sleeves', 'labels' => ['fr_FR' => 'Col rond']]);

        $this->createAssociationType(
            [
                'code' => 'QUANTITY',
                'is_quantified'=> true,
                'labels' => [
                    'fr_FR' => 'Association avec des quantitées'
                ]
            ]
        );

        $this->createProduct('a_product');

        $this->createProductModel(
            [
                'code' => 'apollon',
                'family_variant' => 'clothing_color_size',
                'categories' => ['tshirt'],
                'values' => [
                    'customer_description' => [['data' => 'The beautiful Apollon', 'locale' => 'en_US', 'scope' => null]],
                    'seller_description' => [['data' => 'Apollon is the most beautiful tshirt, buy it', 'locale' => 'en_US', 'scope' => null]],
                    'name' => [['data' => 'apollon', 'locale' => null, 'scope' => null]],
                    'price' => [['data' => [['currency' => 'EUR', 'amount' => '12.3'], ['currency' => 'USD', 'amount' => '12.6']], 'locale' => null, 'scope' => 'ecommerce']],
                    'release_date' => [['data' => '2011-08-21', 'locale' => null, 'scope' => 'ecommerce']],
                ]
            ]
        );

        $this->createProductModel(
            [
                'code' => 'apollon_blue',
                'family_variant' => 'clothing_color_size',
                'parent' => 'apollon',
                'categories' => ['v-neck', 'summer'],
                'values'  => [
                    'colors' => [['data' => ['pink','blue'], 'locale' => null, 'scope' => null]],
                    'main_color'  => [['data' => 'blue', 'locale' => null, 'scope' => null]],
                    'variation_name'  => [['data' => 'my blue tshirt', 'locale' => null, 'scope' => null]],
                ]
            ]
        );

        $this->createProductModel(
            [
                'code' => 'apollon_pink',
                'family_variant' => 'clothing_color_size',
                'parent' => 'apollon',
                'categories' => ['round-neck', 'tshirt'],
                'values'  => [

                    'colors' => [['data' => ['pink','blue'], 'locale' => null, 'scope' => null]],
                    'main_color'  => [['data' => 'pink', 'locale' => null, 'scope' => null]],
                    'variation_name'  => [['data' => 'my pink tshirt', 'locale' => null, 'scope' => null]],
                ],
                'associations' => [
                    'X_SELL' => [
                        'products'=> ['a_product'],
                        'product_models'=> ['apollon'],
                    ]
                ],
                'quantified_associations' => [
                    'QUANTITY' => [
                        'products' => [
                            ['identifier'=> 'a_product', 'quantity' => 12]
                        ],
                        'product_models' => [
                            ['identifier'=> 'apollon', 'quantity' => 5]
                        ]
                    ]
                ]
            ]
        );
    }
}
