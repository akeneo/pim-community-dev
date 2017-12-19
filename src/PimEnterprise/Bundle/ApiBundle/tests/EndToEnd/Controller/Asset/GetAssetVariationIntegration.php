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

use PimEnterprise\Bundle\ApiBundle\Controller\AssetVariationController;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetAssetVariationIntegration extends AbstractAssetTestCase
{
    public function testGetVariationForNonLocalizableAsset()
    {
        $standardizedAssets = $this->getStandardizedAssets();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/assets/non_localizable_asset/variation-files/ecommerce/no_locale');
        $response = $client->getResponse();

        $this->assertSame($response->getStatusCode(), 200);
        $this->assertResponseContent(
            $this->getExpectedVariation($standardizedAssets['non_localizable_asset'], 'ecommerce', null),
            $response->getContent()
        );
    }

    public function testGetVariationForLocalizableAsset()
    {
        $standardizedAssets = $this->getStandardizedAssets();
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/assets/localizable_asset/variation-files/ecommerce/en_US');
        $response = $client->getResponse();

        $this->assertSame($response->getStatusCode(), 200);
        $this->assertResponseContent(
            $this->getExpectedVariation($standardizedAssets['localizable_asset'], 'ecommerce', 'en_US'),
            $response->getContent()
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
            'api/rest/v1/assets/localizable_asset_without_references/variation-files/ecommerce/en_US'
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
            'api/rest/v1/assets/non_localizable_asset_without_references/variation-files/ecommerce/no_locale'
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

        $client->request('GET', 'api/rest/v1/assets/ham_and_jam/variation-files/ecommerce/no_locale');
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
    public function testGivenLocaleDoesNotExist()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            'api/rest/v1/assets/non_localizable_asset_without_references/variation-files/ecommerce/ham'
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
    public function testGivenChannelDoesNotExist()
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'GET',
            'api/rest/v1/assets/non_localizable_asset_without_references/variation-files/jam/en_US'
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
            'api/rest/v1/assets/localizable_asset_without_references/variation-files/ecommerce/no_locale'
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
            'api/rest/v1/assets/non_localizable_asset_without_references/variation-files/ecommerce/en_US'
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
            'api/rest/v1/assets/localizable_asset_without_references/variation-files/ecommerce/fr_FR'
        );
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{
    "code": 404,
    "message": "You cannot have a variation file for the locale \"fr_FR\" and the channel \"ecommerce\" as the locale \"fr_FR\" is not activated for the channel \"ecommerce\"."
}
JSON;

        $this->assertSame(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * @param array  $expected
     * @param string $responseContent
     */
    private function assertResponseContent(array $expected, string $responseContent): void
    {
        $variation = json_decode($responseContent, true);
        ksort($variation);

        $this->assertEquals($expected, $variation);
    }

    /**
     * @param string      $expected
     * @param string      $channel
     * @param null|string $locale
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     *
     * @return array
     */
    private function getExpectedVariation(string $expected, string $channel, ?string $locale): array
    {
        $expected = json_decode($expected, true);
        $expected = $this->sanitizeNormalizedAsset($expected);

        foreach ($expected['variation_files'] as $normalizedVariation) {
            if (null === $locale && $channel === $normalizedVariation['channel']) {
                unset($normalizedVariation['_link']['self']);

                return $normalizedVariation;
            }

            if ($locale === $normalizedVariation['locale'] && $channel === $normalizedVariation['channel']) {
                unset($normalizedVariation['_link']['self']);

                return $normalizedVariation;
            }
        }

        throw new \PHPUnit_Framework_AssertionFailedError(
            sprintf(
                'No asset "%s" variation found for locale "%s"',
                $expected['code'],
                $locale ?? AssetVariationController::NON_LOCALIZABLE_VARIATION
            )
        );
    }
}
