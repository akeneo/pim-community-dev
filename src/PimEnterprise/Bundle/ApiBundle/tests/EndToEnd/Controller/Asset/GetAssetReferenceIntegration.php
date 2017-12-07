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

use PimEnterprise\Bundle\ApiBundle\Controller\AssetReferenceController;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetAssetReferenceIntegration extends AbstractAssetTestCase
{
    public function testGetReferenceForNonLocalizableAsset()
    {
        $standardizedAssets = $this->getStandardizedAssets();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/assets/non_localizable_asset/reference-files/no_locale');
        $response = $client->getResponse();

        $this->assertSame($response->getStatusCode(), 200);
        $this->assertResponseContent(
            $this->getExpectedReference($standardizedAssets['non_localizable_asset'], null),
            $response->getContent()
        );
    }

    public function testGetReferenceForLocalizableAsset()
    {
        $standardizedAssets = $this->getStandardizedAssets();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/assets/localizable_asset/reference-files/en_US');
        $response = $client->getResponse();

        $this->assertSame($response->getStatusCode(), 200);
        $this->assertResponseContent(
            $this->getExpectedReference($standardizedAssets['localizable_asset'], 'en_US'),
            $response->getContent()
        );
    }

    /**
     * Should be an integration test.
     */
    public function testLocalizableAssetReferenceDoesNotExistsForGivenLocale()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/assets/localizable_asset_without_references/reference-files/en_US');
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
            'api/rest/v1/assets/non_localizable_asset_without_references/reference-files/no_locale'
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

        $client->request('GET', 'api/rest/v1/assets/ham_and_jam/reference-files/no_locale');
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

        $client->request('GET', 'api/rest/v1/assets/non_localizable_asset_without_references/reference-files/ham');
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
            'api/rest/v1/assets/localizable_asset_without_references/reference-files/no_locale'
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
            'api/rest/v1/assets/non_localizable_asset_without_references/reference-files/en_US'
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
     * @param array  $expected
     * @param string $responseContent
     */
    private function assertResponseContent(array $expected, string $responseContent): void
    {
        $reference = json_decode($responseContent, true);
        ksort($reference);

        $this->assertEquals($expected, $reference);
    }

    /**
     * @param string      $expected
     * @param null|string $locale
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     *
     * @return array
     */
    private function getExpectedReference(string $expected, ?string $locale): array
    {
        $expected = json_decode($expected, true);
        $expected = $this->sanitizeNormalizedAsset($expected);

        if (null === $locale) {
            $normalizedReference = $expected['reference_files'][0];
            unset($normalizedReference['_link']['self']);

            return $normalizedReference;
        }

        foreach ($expected['reference_files'] as $normalizedReference) {
            if ($locale === $normalizedReference['locale']) {
                unset($normalizedReference['_link']['self']);

                return $normalizedReference;
            }
        }

        throw new \PHPUnit_Framework_AssertionFailedError(sprintf(
            'No asset "%s" reference found for locale "%s"',
            $expected['code'],
            $locale ?? AssetReferenceController::NON_LOCALIZABLE_REFERENCE
        ));
    }
}
