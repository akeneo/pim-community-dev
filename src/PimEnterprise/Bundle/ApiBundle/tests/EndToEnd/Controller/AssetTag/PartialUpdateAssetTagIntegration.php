<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\AssetTag;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class PartialUpdateAssetTagIntegration extends AbstractAssetTagTestCase
{
    public function testUpdateOfAnAssetTag()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
{
    "code": "animal"
}
JSON;
        $client->request('PATCH', 'api/rest/v1/asset-tags/animal', [], [], [], $data);

        $tag = $this->get('pimee_product_asset.repository.tag')->findOneByIdentifier('animal');

        $tagStandard = ['code' => 'animal'];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($tagStandard, ['code' => $tag->getCode()]);
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/asset-tags/animal', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testCreationOfATagWithAnEmptyContent()
    {
        $client = $this->createAuthenticatedClient();

        $data = '{}';

        $client->request('PATCH', 'api/rest/v1/asset-tags/michel', [], [], [], $data);

        $tag = $this->get('pimee_product_asset.repository.tag')->findOneByIdentifier('michel');
        $tagStandard = ['code' => 'michel'];

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($tagStandard, ['code' => $tag->getCode()]);
    }

    /**
     * Should be an integration test.
     */
    public function testResponseWhenValidationFailed()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
{
    "code": "~MICHEL"
}
JSON;

        $expectedContent =
<<<JSON
{
    "code": 422,
    "message": "Validation failed.",
    "errors": [
        {
            "property": "code",
            "message": "Tag code may contain only letters, numbers and underscores"
        }
    ]
}
JSON;

        $client->request('PATCH', 'api/rest/v1/asset-tags/~MICHEL', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenUpdateFailed()
    {
        $client = $this->createAuthenticatedClient();

        $data =
<<<JSON
{
    "michel": "michel"
}
JSON;

        $expectedContent =
<<<JSON
{
    "code": 422,
    "message": "Property \"michel\" does not exist. Check the expected format on the API documentation.",
     "_links": {
         "documentation": {
             "href": "http:\/\/api.akeneo.com\/api-reference.html#patch_asset_tags__code_"
        }
    }
}
JSON;

        $client->request('PATCH', 'api/rest/v1/asset-tags/~MICHEL', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }
}
