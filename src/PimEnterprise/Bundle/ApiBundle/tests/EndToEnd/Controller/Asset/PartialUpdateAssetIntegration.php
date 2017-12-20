<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\Asset;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 */
class PartialUpdateAssetIntegration extends AbstractAssetTestCase
{
    public function testCreationOfAnAsset()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
            {
                "code": "new_asset",
                "categories": ["asset_main_catalog"],
                "description": "This is a nice description.",
                "localizable": true,
                "tags": ["akeneo"],
                "end_of_use": "2016-09-01T00:00:00+0800",
                "variation_files": [
                    {
                        "locale": null,
                        "scope": "ecommerce",
                        "code": "my/code"
                    }
                ],
                "reference_files": [
                    {
                        "locale": null,
                        "code": "my/code"
                    }
                ]
            }
JSON;

        $client->request('PATCH', 'api/rest/v1/assets/new_asset', [], [], [], $data);

        $asset = $this->get('pimee_api.repository.asset')->findOneByIdentifier('new_asset');
        $normalizedAsset = [
            'code'        => 'new_asset',
            'localizable' => true,
            'description' => 'This is a nice description.',
            'end_of_use'  => '2016-08-31T16:00:00+00:00',
            'tags'        => ['akeneo'],
            'categories'  => ['asset_main_catalog'],
        ];
        $normalizer = $this->get('pimee_product_asset.normalizer.standard.asset');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($normalizedAsset, $normalizer->normalize($asset));
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/assets/new_asset', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    public function testUpdateOfAnAsset()
    {
        $this->createTag('ziggy');

        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
            {
                "code": "non_localizable_asset",
                "categories": [],
                "description": "This is a nice description.",
                "localizable": false,
                "tags": ["ziggy"],
                "end_of_use": "2016-09-04T00:00:00+0800",
                "variation_files": [
                    {
                        "locale": null,
                        "scope": "ecommerce",
                        "code": "my/code"
                    }
                ],
                "reference_files": [
                    {
                        "locale": null,
                        "code": "my/code"
                    }
                ]
            }
JSON;

        $client->request('PATCH', 'api/rest/v1/assets/non_localizable_asset', [], [], [], $data);

        $asset = $this->get('pimee_api.repository.asset')->findOneByIdentifier('non_localizable_asset');
        $normalizedAsset = [
            'code'        => 'non_localizable_asset',
            'localizable' => false,
            'description' => 'This is a nice description.',
            'end_of_use'  => '2016-09-03T16:00:00+00:00',
            'tags'        => ['ziggy'],
            'categories'  => [],
        ];
        $normalizer = $this->get('pimee_product_asset.normalizer.standard.asset');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame($normalizedAsset, $normalizer->normalize($asset));
        $this->assertArrayHasKey('location', $response->headers->all());
        $this->assertSame('http://localhost/api/rest/v1/assets/non_localizable_asset', $response->headers->get('location'));
        $this->assertSame('', $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testResponseWhenContentIsEmpty()
    {
        $client = $this->createAuthenticatedClient();

        $data = '';

        $expectedContent = <<<JSON
            {
                "code": 400,
                "message":  "Invalid json message received"
            }
JSON;

        $client->request('PATCH', 'api/rest/v1/assets/new_code', [], [], [], $data);
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testResponseWhenValidationFailed()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
            {
                "code": "new_code",
                "categories": ["asset_main_catalog"],
                "description": "This is a nice description.",
                "localizable": false,
                "tags": ["akeneo"],
                "end_of_use": "2016-09-01T00:00:00+0300"
            }
JSON;

        $expectedContent = <<<JSON
            {
                "code": 422,
                "message": "Validation failed.",
                "errors": [
                    {
                        "property": "code",
                        "message": "This property cannot be changed."
                    }
                ]
            }
JSON;

        $client->request('PATCH', 'api/rest/v1/assets/non_localizable_asset', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testResponseWhenUpdateOfAnAssetFailed()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
            {
                "code": "non_localizable_asset",
                "categories": ["unknown_category"],
                "description": "This is a nice description.",
                "localizable": true,
                "tags": ["akeneo"],
                "end_of_use": "2016-09-01T00:00:00+0300"
            }
JSON;

        $expectedContent = <<<JSON
            {
                "code": 422,
                "message": "Property \"categories\" expects a valid category code. The category does not exist, \"unknown_category\" given. Check the expected format on the API documentation.",
                "_links": {
                    "documentation": {
                        "href": "http://api.akeneo.com/api-reference.html#post_asset"
                    }
                }
            }
JSON;

        $client->request('PATCH', 'api/rest/v1/assets/non_localizable_asset', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testResponseWhenAnAssetIsCreatedWithInconsistentCodes()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
            {
                "code": "inconsistent_code2"
            }
JSON;

        $expectedContent = <<<JSON
            {
                "code": 422,
                "message": "The code \"inconsistent_code2\" provided in the request body must match the code \"inconsistent_code1\" provided in the url."
            }
JSON;

        $client->request('PATCH', 'api/rest/v1/assets/inconsistent_code1', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }
}
