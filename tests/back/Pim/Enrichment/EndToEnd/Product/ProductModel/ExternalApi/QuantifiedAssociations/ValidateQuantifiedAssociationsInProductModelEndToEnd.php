<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi\QuantifiedAssociations;

use Akeneo\Test\Integration\Configuration;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\EntityWithQuantifiedAssociations\QuantifiedAssociationsTestCaseTrait;
use AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\Response;

class ValidateQuantifiedAssociationsInProductModelEndToEnd extends AbstractProductTestCase
{
    use QuantifiedAssociationsTestCaseTrait;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_quantified_associations_format_is_invalid(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->createQuantifiedAssociationType('PRODUCTSET');
        $this->createProduct('chair', []);
        $code = 'garden_table_set';

        $data = <<<JSON
{
    "identifier": "$code",
    "family_variant": "familyVariantA1",
    "quantified_associations": {
        "PRODUCTSET": [
            {"identifier": "chair", "quantity": 4}
        ]
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/product-models', [], [], [], $data);

        $expectedContent = [
            'code' => 422,
            'message' => 'Property "quantified_associations" expects an array of arrays as data. Check the expected format on the API documentation.',
            '_links' => [
                'documentation' => [
                    'href' => 'http://api.akeneo.com/api-reference.html#post_product_model'
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }
}
