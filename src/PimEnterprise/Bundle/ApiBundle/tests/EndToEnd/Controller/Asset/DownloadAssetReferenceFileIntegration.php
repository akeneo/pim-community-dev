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
class DownloadAssetReferenceFileIntegration extends AbstractAssetTestCase
{
    public function testGetReferenceForNonLocalizableAsset()
    {
        $client = $this->createAuthenticatedClient();

        $contentFile = '';
        ob_start(function ($streamedFile) use (&$contentFile) {
            $contentFile .= $streamedFile;

            return '';
        });
        $client->request('GET', 'api/rest/v1/assets/non_localizable_asset/reference-files/no_locale/download');
        ob_end_clean();

        $response = $client->getResponse();
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame('attachment; filename="ziggy.png"', $response->headers->get('content-disposition'));
        $this->assertSame('image/png', $response->headers->get('content-type'));
        $this->assertEquals($contentFile, file_get_contents($this->getFixturePath('ziggy.png')));
    }

    public function testGetReferenceForLocalizableAsset()
    {
        $client = $this->createAuthenticatedClient();

        $contentFile = '';
        ob_start(function ($streamedFile) use (&$contentFile) {
            $contentFile .= $streamedFile;

            return '';
        });
        $client->request('GET', 'api/rest/v1/assets/localizable_asset/reference-files/en_US/download');
        ob_end_clean();

        $response = $client->getResponse();
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame('attachment; filename="ziggy.png"', $response->headers->get('content-disposition'));
        $this->assertSame('image/png', $response->headers->get('content-type'));
        $this->assertEquals($contentFile, file_get_contents($this->getFixturePath('ziggy.png')));
    }

    /**
     * Should be an integration test.
     */
    public function testLocalizableAssetReferenceDoesNotExistsForGivenLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            'api/rest/v1/assets/localizable_asset_without_references/reference-files/en_US/download'
        );
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{
    "code": 404,
    "message": "Reference file for the asset \"localizable_asset_without_references\" and the locale \"en_US\" does not exist."
}
JSON;

        $this->assertSame(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testAssetReferenceDoesNotExists()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            'api/rest/v1/assets/non_localizable_asset_without_references/reference-files/no_locale/download'
        );
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{
    "code": 404,
    "message": "Reference file for the asset \"non_localizable_asset_without_references\" does not exist."
}
JSON;

        $this->assertSame(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testAssetDoesNotExists()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/assets/ham_and_jam/reference-files/no_locale/download');
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

    /**
     * Should be an integration test.
     */
    public function testLocaleDoesNotExist()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            'api/rest/v1/assets/non_localizable_asset_without_references/reference-files/ham/download'
        );
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{
    "code": 404,
    "message": "Locale \"ham\" does not exist."
}
JSON;

        $this->assertSame(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testThatItFailsWhenNoLocaleProvidedToGetLocalizableAsset()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            'api/rest/v1/assets/localizable_asset_without_references/reference-files/no_locale/download'
        );
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{
    "code": 422,
    "message": "The asset \"localizable_asset_without_references\" is localizable, you must provide an existing locale code. \"no_locale\" is only allowed when the asset is not localizable."
}
JSON;

        $this->assertSame(422, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testThatItFailsWhenLocaleProvidedToGetNonLocalizableAsset()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            'api/rest/v1/assets/non_localizable_asset_without_references/reference-files/en_US/download'
        );
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{
    "code": 422,
    "message": "The asset \"non_localizable_asset_without_references\" is not localizable, you must provide the string \"no_locale\" as a locale."
}
JSON;

        $this->assertSame(422, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }
}
