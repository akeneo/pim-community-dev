<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\VariantProduct\ExternalApi\QuantifiedAssociations;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class UpdateQuantifiedAssociationsInVariantProductEndToEnd extends AbstractProductTestCase
{
    use QuantifiedAssociationsTestCaseTrait;

    /**
     * @test
     */
    public function it_can_partial_update_quantified_associations_in_a_variant_product(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->createQuantifiedAssociationType('PRODUCTSET');
        $chairUuid = $this->createProduct('chair')->getUuid();
        $this->createProductModel([
            'code' => 'garden_table_set',
            'family_variant' => 'familyVariantA1',
            'values' => [],
        ]);

        $this->createProductModel([
            'code' => 'garden_table_set-black',
            'parent' => 'garden_table_set',
            'family_variant' => 'familyVariantA1',
            'values' => [
                'a_simple_select' => [['locale' => null, 'scope' => null, 'data' => 'optionB']],
            ],
        ]);

        $this->createProductModel([
            'code' => 'umbrella',
            'family_variant' => 'familyVariantA1',
            'values' => [],
        ]);

        $this->createVariantProduct('garden_table_set-black-gold', [
            new ChangeParent('garden_table_set-black'),
            new SetBooleanValue('a_yes_no', null, null, true),
            new AssociateQuantifiedProducts('PRODUCTSET', [new QuantifiedEntity('chair', 4)]),
            new AssociateQuantifiedProductModels('PRODUCTSET', [
                new QuantifiedEntity('umbrella', 4)
            ])
        ]);

        $data = <<<JSON
{
    "identifier": "garden_table_set-black-gold",
    "quantified_associations": {
        "PRODUCTSET": {
            "products": [
                {"identifier": "chair", "quantity": 6}
            ]
        }
    }
}
JSON;

        $client->request('PATCH', sprintf('/api/rest/v1/products/%s', 'garden_table_set-black-gold'), [], [], [], $data);

        $expectedProduct = [
            'identifier' => 'garden_table_set-black-gold',
            'family' => 'familyA',
            'parent' => 'garden_table_set-black',
            'groups' => [],
            'categories' => [],
            'enabled' => true,
            'values' => [
                "a_simple_select" => [
                    ["data" => "optionB", "locale" => null, "scope" => null]
                ],
                "a_yes_no"=>[
                    ["data" => true, "locale" => null, "scope" => null]
                ],
                "sku" => [
                    ["data" => "garden_table_set-black-gold", "locale" => null, "scope" => null]
                ]
            ],
            'created' => '2016-06-14T13:12:50+02:00',
            'updated' => '2016-06-14T13:12:50+02:00',
            'associations' => [],
            'quantified_associations' => [
                'PRODUCTSET' => [
                    'products' => [
                        ['uuid' => $chairUuid->toString(), 'identifier' => 'chair', 'quantity' => 6],
                    ],
                    'product_models' => [
                        ['identifier' => 'umbrella', 'quantity' => 4],
                    ],
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSameProducts($expectedProduct, 'garden_table_set-black-gold');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
