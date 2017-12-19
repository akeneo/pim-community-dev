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
class DownloadAssetVariationFileIntegration extends AbstractAssetTestCase
{
    public function testGetVariationForNonLocalizableAsset()
    {
        $client = $this->createAuthenticatedClient();

        $contentFile = '';
        ob_start(function ($streamedFile) use (&$contentFile) {
            $contentFile .= $streamedFile;

            return '';
        });
        $client->request(
            'GET',
            'api/rest/v1/assets/non_localizable_asset/variation-files/ecommerce/no_locale/download'
        );
        ob_end_clean();

        $response = $client->getResponse();
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame(
            'attachment; filename="ziggy-ecommerce.png"',
            $response->headers->get('content-disposition')
        );
        $this->assertSame('image/png', $response->headers->get('content-type'));
        $this->assertEquals(
            $contentFile,
            file_get_contents($this->getVariationFile('non_localizable_asset', 'ecommerce', null))
        );
    }

    public function testGetVariationForLocalizableAsset()
    {
        $client = $this->createAuthenticatedClient();

        $contentFile = '';
        ob_start(function ($streamedFile) use (&$contentFile) {
            $contentFile .= $streamedFile;

            return '';
        });
        $client->request('GET', 'api/rest/v1/assets/localizable_asset/variation-files/ecommerce/en_US/download');
        ob_end_clean();

        $response = $client->getResponse();
        $this->assertSame($response->getStatusCode(), 200);
        $this->assertSame(
            'attachment; filename="ziggy-en_US-ecommerce.png"',
            $response->headers->get('content-disposition')
        );
        $this->assertSame('image/png', $response->headers->get('content-type'));
        $this->assertEquals(
            $contentFile,
            file_get_contents($this->getVariationFile('localizable_asset', 'ecommerce', 'en_US'))
        );
    }

    /**
     * Should be an integration test.
     */
    public function testLocalizableAssetVariationDoesNotExistsForGivenLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            'api/rest/v1/assets/localizable_asset_without_references/variation-files/ecommerce/en_US/download'
        );
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{
    "code": 404,
    "message": "Variation file for the asset \"localizable_asset_without_references\" and the channel \"ecommerce\" and the locale \"en_US\" does not exist."
}
JSON;

        $this->assertSame(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testAssetVariationDoesNotExists()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            'api/rest/v1/assets/non_localizable_asset_without_references/variation-files/ecommerce/no_locale/download'
        );
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{
    "code": 404,
    "message": "Variation file for the asset \"non_localizable_asset_without_references\" and the channel \"ecommerce\" does not exist."
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

        $client->request('GET', 'api/rest/v1/assets/ham_and_jam/variation-files/ecommerce/no_locale/download');
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
            'api/rest/v1/assets/non_localizable_asset_without_references/variation-files/ecommerce/ham/download'
        );
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{
    "code": 404,
    "message": "Locale \"ham\" does not exist or is not activated."
}
JSON;

        $this->assertSame(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testChannelDoesNotExist()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            'api/rest/v1/assets/non_localizable_asset_without_references/variation-files/jam/en_US/download'
        );
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{
    "code": 404,
    "message": "Channel \"jam\" does not exist."
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
            'api/rest/v1/assets/localizable_asset_without_references/variation-files/ecommerce/no_locale/download'
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
            'api/rest/v1/assets/non_localizable_asset_without_references/variation-files/ecommerce/en_US/download'
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

    /**
     * Should be an integration test.
     */
    public function testLocaleIsNotActivated()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            'api/rest/v1/assets/localizable_asset_without_references/variation-files/ecommerce/fr_FR/download'
        );
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{
    "code": 404,
    "message": "There is no variation file for the locale \"fr_FR\" and the channel \"ecommerce\" as the locale \"fr_FR\" is not activated for the channel \"ecommerce\"."
}
JSON;

        $this->assertSame(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * Returns the real path to an asset variation file for a given channel and
     * locale (locale is provided only if the asset is localizable, must be null
     * otherwise).
     *
     * @param string      $assetCode
     * @param string      $channelCode
     * @param null|string $localeCode
     *
     * @return string
     */
    private function getVariationFile(string $assetCode, string $channelCode, ?string $localeCode): string
    {
        $asset = $this->get('pimee_api.repository.asset')->findOneByIdentifier($assetCode);
        $this->assertNotNull($asset);

        $channel = $this->get('pim_api.repository.channel')->findOneByIdentifier($channelCode);
        $this->assertNotNull($channel);

        $locale = null;
        if (null !== $localeCode) {
            $locale = $this->get('pim_api.repository.locale')->findOneByIdentifier($localeCode);
            $this->assertNotNull($locale);
        }

        $variation = $asset->getVariation($channel, $locale);
        $this->assertNotNull($variation);

        $fileInfo = $variation->getFileInfo();
        $this->assertNotNull($variation);

        $variationFileRealPath = sprintf(
            '%s/%s',
            $this->getParameter('asset_storage_dir'),
            $fileInfo->getKey()
        );

        return realpath($variationFileRealPath);
    }
}
