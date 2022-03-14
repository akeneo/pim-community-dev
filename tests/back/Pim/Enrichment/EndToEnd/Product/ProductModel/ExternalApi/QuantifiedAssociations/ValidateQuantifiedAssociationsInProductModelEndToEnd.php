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
    public function it_returns_an_error_if_the_quantified_associations_data_is_invalid(): void
    {
        $client = $this->createAuthenticatedClient();
        $code = 'garden_table_set';

        $data = <<<JSON
{
    "code": "$code",
    "family_variant": "familyVariantA1",
    "quantified_associations": {
        "THIS_ASSOCIATION_TYPE_DOES_NOT_EXISTS": {
            "products": []
        }
    }
}
JSON;

        $client->request('POST', '/api/rest/v1/product-models', [], [], [], $data);

        $expectedContent = [
            'code' => 422,
            'message' => 'Validation failed.',
            'errors' => [
                [
                    'property' => 'quantifiedAssociations.THIS_ASSOCIATION_TYPE_DOES_NOT_EXISTS',
                    'message' => 'This association type doesn\'t exist. Please make sure it hasn\'t been deleted in the meantime.',
                ],
            ],
        ];

        $response = $client->getResponse();

        $this->assertSame($expectedContent, json_decode($response->getContent(), true));
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }
}
