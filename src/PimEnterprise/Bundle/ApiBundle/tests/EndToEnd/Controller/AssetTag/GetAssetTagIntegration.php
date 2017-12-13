<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\AssetTag;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\Asset\AbstractAssetTestCase;

/**
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class GetAssetTagIntegration extends AbstractAssetTestCase
{
    public function testGetAnAssetTag()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/asset-tags/akeneo');
        $response = $client->getResponse();

        $assetTag = <<<JSON
    {
        "code": "akeneo"
    }
JSON;

        $this->assertSame($response->getStatusCode(), 200);
        $this->assertJsonStringEqualsJsonString($assetTag, $response->getContent());
    }

    public function testAssetDoesNotExists()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/asset-tags/michel');
        $response = $client->getResponse();

        $expectedContent = <<<JSON
    {
        "code": 404,
        "message": "Tag \"michel\" does not exist."
    }
JSON;

        $this->assertSame(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }
}
