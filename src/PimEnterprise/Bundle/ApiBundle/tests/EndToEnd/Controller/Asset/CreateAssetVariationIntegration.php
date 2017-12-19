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

use League\Flysystem\FilesystemInterface;
use PHPUnit\Framework\Assert;
use PimEnterprise\Component\ProductAsset\FileStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 */
class CreateAssetVariationIntegration extends AbstractAssetTestCase
{
    /** @var array */
    private $files = [];

    /*** @var FilesystemInterface */
    private $fileSystem;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $product = $this->getFromTestContainer('pim_catalog.builder.product')->createProduct('foo');
        $this->getFromTestContainer('akeneo_storage_utils.doctrine.object_detacher')->detach($product);

        $this->files['akeneo'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.jpg';
        copy($this->getFixturePath('akeneo.jpg'), $this->files['akeneo']);

        $this->files['ziggy'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ziggy.png';
        copy($this->getFixturePath('ziggy.png'), $this->files['ziggy']);

        $mountManager = $this->getFromTestContainer('oneup_flysystem.mount_manager');
        $this->fileSystem = $mountManager->getFilesystem(FileStorage::ASSET_STORAGE_ALIAS);
    }

    public function testUpdateAVariationFileOnLocalizableAsset()
    {
        $this->assertCorrectlyCreatedAssetVariation(
            $this->files['ziggy'],
            'ziggy_variation.png',
            'image/png',
            'localizable_asset',
            'ecommerce',
            'en_US',
            204
        );
    }

    public function testUpdateAVariationFileOnNotLocalizableAsset()
    {
        $this->assertCorrectlyCreatedAssetVariation(
            $this->files['ziggy'],
            'ziggy_variation.png',
            'image/png',
            'non_localizable_asset',
            'ecommerce',
            'no_locale',
            204
        );
    }

    /**
     * When creating an asset, all variations object are created for all the channels.
     * If you add a channel, new variation objects are not created automatically for the assets.
     *
     * This test checks that it's still possible to add a variation for this new channel.
     */
    public function testCreateAVariationFileOnNewChannelForAnExistingAssetWithReferences()
    {
        $channel = $this->get('pim_catalog.factory.channel')->create();
        $this->get('pim_catalog.updater.channel')->update(
            $channel,
            [
                'code'          => 'new_channel',
                'locales'       => ['en_US', 'fr_FR'],
                'currencies'    => ['EUR', 'USD'],
                'category_tree' => 'master',
            ]
        );
        $this->get('pim_catalog.saver.channel')->save($channel);


        $this->assertCorrectlyCreatedAssetVariation(
            $this->files['akeneo'],
            'akeneo.jpg',
            'image/jpeg',
            'non_localizable_asset',
            'new_channel',
            'no_locale',
            201
        );
    }

    /**
     * When creating an asset, all variations object are created for all the channels/locales.
     * Also, all reference objects are created for all activated locales.
     * If you activate a new locale,the new reference object is not created automatically for the assets, as for the variation.
     *
     * This test checks that it's still possible to add a variation for when activating a new locale.
     */
    public function testCreateAVariationFileOnAssetWithoutReferenceForTheLocale()
    {
        $channel = $this->get('pim_api.repository.channel')->findOneByIdentifier('ecommerce');
        $this->get('pim_catalog.updater.channel')->update($channel, ['locales' => ['en_US', 'fr_FR']]);
        $this->get('pim_catalog.saver.channel')->save($channel);


        $this->assertCorrectlyCreatedAssetVariation(
            $this->files['akeneo'],
            'akeneo.jpg',
            'image/jpeg',
            'localizable_asset',
            'ecommerce',
            'fr_FR',
            201
        );
    }

    /**
     * Should be an integration test.
     */
    public function testErrorMessageWhenCreatingVariationOnNonExistingAsset()
    {
        $this->assertError(
            'api/rest/v1/assets/foo/variation-files/ecommerce/en_US',
            404,
            'Asset \"foo\" does not exist.'
        );
    }

    /**
     * Should be an integration test.
     */
    public function testErrorMessageWhenCreatingVariationOnNonExistingChannel()
    {
        $this->assertError(
            'api/rest/v1/assets/localizable_asset/variation-files/foo/en_US',
            404,
            'Channel \"foo\" does not exist.'
        );
    }

    /**
     * Should be an integration test.
     */
    public function testErrorMessageWhenCreatingVariationOnNonExistingLocale()
    {
        $this->assertError(
            'api/rest/v1/assets/localizable_asset/variation-files/ecommerce/foo',
            404,
            'Locale \"foo\" does not exist or is not activated.'
        );
    }

    /**
     * Should be an integration test.
     */
    public function testErrorMessageWhenCreatingLocalizableVariationWithNoLocale()
    {
        $this->assertError(
            'api/rest/v1/assets/localizable_asset/variation-files/ecommerce/no_locale',
            422,
            'The asset \"localizable_asset\" is localizable, you must provide an existing locale code. \"no_locale\" is only allowed when the asset is not localizable.'
        );
    }

    /**
     * Should be an integration test.
     */
    public function testErrorMessageWhenCreatingNonLocalizableVariationWithLocale()
    {
        $this->assertError(
            'api/rest/v1/assets/non_localizable_asset/variation-files/ecommerce/en_US',
            422,
            'The asset \"non_localizable_asset\" is not localizable, you must provide the string \"no_locale\" as a locale.'
        );
    }

    /**
     * Should be an integration test.
     */
    public function testErrorMessageWhenCreatingVariationOnLocaleNotActivatedForTheChannel()
    {
        $this->assertError(
            'api/rest/v1/assets/localizable_asset/variation-files/ecommerce/de_DE',
            404,
            'You cannot have a variation file for the locale \"de_DE\" and the channel \"ecommerce\" as the locale \"de_DE\" is not activated for the channel \"ecommerce\".'
        );
    }

    /**
     * Should be an integration test.
     */
    public function testErrorMessageWhenCreatingVariationWithoutFile()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $client->request('PATCH', 'api/rest/v1/assets/localizable_asset/variation-files/ecommerce/en_US');
        $response = $client->getResponse();

        $expectedContent = <<<JSON
            {
                "code": 422,
                "message": "Property \"file\" is required."
            }
JSON;

        $this->assertSame(422, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * @param string $pathFile
     *
     * @return bool
     */
    protected function doesFileExist(string $pathFile): bool
    {
        return $this->fileSystem->has($pathFile);
    }

    /**
     * @param string $pathFile
     */
    protected function unlinkFile(string $pathFile): void
    {
        if ($this->fileSystem->has($pathFile)) {
            $this->fileSystem->delete($pathFile);
        }
    }

    /**
     * @param string $filePath
     * @param string $fileName
     * @param string $mimeType
     * @param string $assetCode
     * @param string $channelCode
     * @param string $localeCode
     * @param int    $status
     */
    private function assertCorrectlyCreatedAssetVariation(
        string $filePath,
        string $fileName,
        string $mimeType,
        string $assetCode,
        string $channelCode,
        string$localeCode,
        int $status
    ): void {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $file = new UploadedFile($filePath, $fileName);

        $client->request('PATCH', sprintf('api/rest/v1/assets/%s/variation-files/%s/%s', $assetCode, $channelCode, $localeCode), [], ['file' => $file]);
        $response = $client->getResponse();

        Assert::assertSame($status, $response->getStatusCode());
        Assert::assertEmpty($response->getContent());
        Assert::assertArrayHasKey('location', $response->headers->all());
        Assert::assertSame(
            sprintf('http://localhost/api/rest/v1/assets/%s/variation-files/%s/%s', $assetCode, $channelCode, $localeCode),
            $response->headers->get('location')
        );

        $this->get('doctrine.orm.default_entity_manager')->clear();

        $locale = $this->get('pim_api.repository.locale')->findOneByIdentifier($localeCode);
        $channel = $this->get('pim_api.repository.channel')->findOneByIdentifier($channelCode);
        $asset = $this->get('pimee_api.repository.asset')->findOneByIdentifier($assetCode);
        $variation = $asset->getVariation($channel, $locale);

        $fileInfo = $variation->getFileInfo();
        Assert::assertTrue($variation->isLocked());
        Assert::assertSame($fileInfo->getKey(), $variation->getSourceFileInfo()->getKey());
        Assert::assertSame($fileName, $fileInfo->getOriginalFilename());
        Assert::assertSame($mimeType, $fileInfo->getMimeType());
        Assert::assertSame('assetStorage', $fileInfo->getStorage());
        Assert::assertTrue($this->doesFileExist($fileInfo->getKey()));

        $this->unlinkFile($fileInfo->getKey());
    }

    /**
     * @param string $url
     * @param int    $errorCode
     * @param string $message
     */
    private function assertError(string $url, int $errorCode, string $message)
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $file = new UploadedFile($this->files['ziggy'], 'ziggy.png');

        $client->request('PATCH', $url, [], ['file' => $file]);
        $response = $client->getResponse();

        $expectedContent = <<<JSON
{
    "code": {$errorCode},
    "message": "{$message}"
}
JSON;

        $this->assertSame($errorCode, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }
}
