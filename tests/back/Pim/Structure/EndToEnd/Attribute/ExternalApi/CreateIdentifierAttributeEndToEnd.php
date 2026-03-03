<?php

namespace AkeneoTest\Pim\Structure\EndToEnd\Attribute\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateIdentifierAttributeEndToEnd extends ApiTestCase
{
    public function testCreateIdentifierAttribute(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code":"my_identifier",
        "type":"pim_catalog_identifier",
        "group":"other",
        "useable_as_grid_filter": true
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    public function testCannotCreateEleventhIdentifierAttribute(): void
    {
        $this->createANumberOfIdentifierAttributes(9);

        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code":"my_identifier",
        "type":"pim_catalog_identifier",
        "group":"other",
        "useable_as_grid_filter": true
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();

        $expectedContent = [
            'code'    => 422,
            'message' => 'Validation failed.',
            'errors'  => [
                [
                    'property' => '',
                    'message'  => 'Limit of "10" identifier attributes is reached. The following identifier has not been created ',
                ],
            ],
        ];

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode($expectedContent), $response->getContent());
    }

    public function testCannotCreateMoreThanTenIdentifiersThroughPatchMultiple(): void
    {
        $this->createANumberOfIdentifierAttributes(5);

        $data = <<<JSON
    {"code":"one", "type":"pim_catalog_identifier", "group":"other", "useable_as_grid_filter": true}
    {"code":"two", "type":"pim_catalog_identifier", "group":"other", "useable_as_grid_filter": true}
    {"code":"three", "type":"pim_catalog_identifier", "group":"other", "useable_as_grid_filter": true}
    {"code":"four", "type":"pim_catalog_identifier", "group":"other", "useable_as_grid_filter": true}
    {"code":"five", "type":"pim_catalog_identifier", "group":"other", "useable_as_grid_filter": true}
    {"code":"six", "type":"pim_catalog_identifier", "group":"other", "useable_as_grid_filter": true}
JSON;

        $expectedResponse = <<<JSON
{"line":1,"code":"one","status_code":201}
{"line":2,"code":"two","status_code":201}
{"line":3,"code":"three","status_code":201}
{"line":4,"code":"four","status_code":201}
{"line":5,"code":"five","status_code":422,"message":"Validation failed.","errors":[{"property":"","message":"Limit of \"10\" identifier attributes is reached. The following identifier has not been created "}]}
{"line":6,"code":"six","status_code":422,"message":"Validation failed.","errors":[{"property":"","message":"Limit of \"10\" identifier attributes is reached. The following identifier has not been created "}]}
JSON;

        $response = $this->executeStreamRequest('PATCH', 'api/rest/v1/attributes', [], [], [], $data);
        $httpResponse = $response['http_response'];

        $this->assertSame(Response::HTTP_OK, $httpResponse->getStatusCode());
        $this->assertSame($expectedResponse, $response['content']);
    }

    public function testCannotSetAnIdentifierAsMain(): void
    {
        $client = $this->createAuthenticatedClient();

        $data =
            <<<JSON
    {
        "code":"my_identifier",
        "type":"pim_catalog_identifier",
        "group":"other",
        "useable_as_grid_filter": true,
        "is_main_identifier": true
    }
JSON;

        $client->request('POST', 'api/rest/v1/attributes', [], [], [], $data);

        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame('', $response->getContent());

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('my_identifier');
        $this->assertFalse($attribute->isMainIdentifier());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createANumberOfIdentifierAttributes(int $number): void
    {
        for ($i = 0; $i < $number; $i++) {
            $attribute = self::getContainer()->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update($attribute, [
                'code' => 'identifier_' . $i,
                'type' => 'pim_catalog_identifier',
                'group' => 'other',
                'useable_as_grid_filter' => true
            ]);
            $violations = $this->get('validator')->validate($attribute);
            $this->assertSame(0, $violations->count(), (string)$violations);
            $this->get('pim_catalog.saver.attribute')->save($attribute);
        }
    }
}
