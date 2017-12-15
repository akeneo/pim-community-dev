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
class CreateAssetIntegration extends AbstractAssetTestCase
{
    public function testCreationOfAnAsset()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
            {
                "code": "new_asset",
                "categories": ["asset_main_catalog"],
                "description": "This is a nice description.",
                "localized": true,
                "tags": ["akeneo"],
                "end_of_use": "2016-09-01T00:00:00+0800",
                "variation_files": [
                    {
                        "locale": null,
                        "channel": "ecommerce",
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

        $client->request('POST', 'api/rest/v1/assets', [], [], [], $data);

        $asset = $this->get('pimee_api.repository.asset')->findOneByIdentifier('new_asset');
        $normalizedAsset = [
            'code'        => 'new_asset',
            'localized'   => true,
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

        $client->request('POST', 'api/rest/v1/assets', [], [], [], $data);
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
                "code": "non_localizable_asset",
                "categories": ["asset_main_catalog"],
                "description": "This is a nice description.",
                "localized": true,
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
                        "message": "This value is already used."
                    }
                ]
            }
JSON;

        $client->request('POST', 'api/rest/v1/assets', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testResponseWhenUpdatingFailed()
    {
        $client = $this->createAuthenticatedClient();

        $data = <<<JSON
            {
                "code": "non_localizable_asset",
                "categories": ["unknown_category"],
                "description": "This is a nice description.",
                "localized": true,
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

        $client->request('POST', 'api/rest/v1/assets', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }
}
