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

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetAssetIntegration extends AbstractAssetTestCase
{
    public function testAssetDoesNotExists()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/assets/ham_and_jam');
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{
    "code": 404,
    "message": "Asset \"ham_and_jam\" does not exist."
}
JSON;

        $this->assertSame(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testGetNonLocalizableAsset()
    {
        $standardizedAssets = $this->getStandardizedAssets();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/assets/non_localizable_asset');
        $response = $client->getResponse();

        $this->assertSame($response->getStatusCode(), 200);
        $this->assertResponseContent($response->getContent(), $standardizedAssets['non_localizable_asset']);
    }

    public function testGetLocalizableAsset()
    {
        $standardizedAssets = $this->getStandardizedAssets();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/assets/localizable_asset');
        $response = $client->getResponse();

        $this->assertSame($response->getStatusCode(), 200);
        $this->assertResponseContent($response->getContent(), $standardizedAssets['localizable_asset']);
    }

    /**
     * @param string $responseContent
     * @param        $expected
     */
    private function assertResponseContent(string $responseContent, $expected): void
    {
        $expected = json_decode($expected, true);
        $result = json_decode($responseContent, true);

        $expected = $this->sanitizeNormalizedAsset($expected);
        $result = $this->sanitizeNormalizedAsset($result);

        $this->assertEquals($expected, $result);
    }
}
