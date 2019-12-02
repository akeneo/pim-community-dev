<?php

namespace AkeneoTestEnterprise\Asset\EndToEnd\ExternalApi\AssetCategory;

use Akeneo\Test\Integration\Configuration;
use AkeneoTestEnterprise\Asset\EndToEnd\ExternalApi\Asset\AbstractAssetTestCase;
use Symfony\Component\HttpFoundation\Response;

class SearchAfterListAssetIntegration extends AbstractAssetTestCase
{
    /**
     * Should be an integration test.
     */
    public function testUnknownPaginationType()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', 'api/rest/v1/assets?pagination_type=unknown');

        $response = $client->getResponse();

        $expected = '{"code": 422,"message":"Pagination type does not exist."}';

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    protected function getStandardizedAssetsWithLinks(): array
    {
        $assets = $this->getStandardizedAssets();
        $assetsWithLinks = [];
        foreach ($assets as $code => $jsonAsset) {
            $asset = json_decode($jsonAsset, true);
            $asset['_links']['self']['href'] = sprintf('http://localhost/api/rest/v1/assets/%s', $code);
            $assetsWithLinks[$code] = json_encode($asset);
        }

        return $assetsWithLinks;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @param string $url
     * @param string $expected
     */
    private function assert(string $url, string $expected)
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }
}
