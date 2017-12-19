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
class CreateAssetReferenceIntegration extends AbstractAssetTestCase
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

    public function testUpdateAReferenceFileOnLocalizableAsset()
    {
        $this->assertCorrectlyCreatedAssetReference(
            $this->files['ziggy'],
            'ziggy.png',
            'image/png',
            'localizable_asset',
            'en_US',
            204
        );
    }

    public function testUpdateAReferenceFileOnNotLocalizableAsset()
    {
        $this->assertCorrectlyCreatedAssetReference(
            $this->files['ziggy'],
            'ziggy.png',
            'image/png',
            'non_localizable_asset',
            'no_locale',
            204
        );
    }

    public function testCreateAReferenceFile()
    {
        $this->assertCorrectlyCreatedAssetReference(
            $this->files['ziggy'],
            'ziggy.png',
            'image/png',
            'localizable_asset_without_references',
            'en_US',
            201
        );
    }

    /**
     * Should be an integration test.
     */
    public function testErrorMessageWhenCreatingReferenceOnNonExistingAsset()
    {
        $this->assertError(
            'api/rest/v1/assets/foo/reference-files/en_US',
            404,
            'Asset \"foo\" does not exist.'
        );
    }

    /**
     * Should be an integration test.
     */
    public function testErrorMessageWhenCreatingReferenceOnNonExistingLocale()
    {
        $this->assertError(
            'api/rest/v1/assets/localizable_asset/reference-files/foo',
            404,
            'Locale \"foo\" does not exist or is not activated.'
        );
    }

    /**
     * Should be an integration test.
     */
    public function testErrorMessageWhenCreatingLocalizableReferenceWithNoLocale()
    {
        $this->assertError(
            'api/rest/v1/assets/localizable_asset/reference-files/no_locale',
            422,
            'The asset \"localizable_asset\" is localizable, you must provide an existing locale code. \"no_locale\" is only allowed when the asset is not localizable.'
        );
    }

    /**
     * Should be an integration test.
     */
    public function testErrorMessageWhenCreatingNonLocalizableReferenceWithLocale()
    {
        $this->assertError(
            'api/rest/v1/assets/non_localizable_asset/reference-files/en_US',
            422,
            'The asset \"non_localizable_asset\" is not localizable, you must provide the string \"no_locale\" as a locale.'
        );
    }

    /**
     * Should be an integration test.
     */
    public function testErrorMessageWhenCreatingReferenceWithoutFile()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $client->request('PATCH', 'api/rest/v1/assets/localizable_asset/reference-files/en_US');
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
     * @param string $localeCode
     * @param int    $status
     */
    private function assertCorrectlyCreatedAssetReference(
        string $filePath,
        string $fileName,
        string $mimeType,
        string $assetCode,
        string$localeCode,
        int $status
    ): void {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $file = new UploadedFile($filePath, $fileName);

        $client->request('PATCH', sprintf('api/rest/v1/assets/%s/reference-files/%s', $assetCode, $localeCode), [], ['file' => $file]);
        $response = $client->getResponse();

        Assert::assertSame($status, $response->getStatusCode());
        Assert::assertEmpty($response->getContent());
        Assert::assertArrayHasKey('location', $response->headers->all());
        Assert::assertSame(
            sprintf('http://localhost/api/rest/v1/assets/%s/reference-files/%s', $assetCode, $localeCode),
            $response->headers->get('location')
        );

        $this->get('doctrine.orm.default_entity_manager')->clear();

        $locale = $this->get('pim_api.repository.locale')->findOneByIdentifier($localeCode);
        $asset = $this->get('pimee_api.repository.asset')->findOneByIdentifier($assetCode);
        $reference = $asset->getReference($locale);
        $fileInfo = $reference->getFileInfo();

        Assert::assertNotNull($fileInfo);
        Assert::assertSame($fileName, $fileInfo->getOriginalFilename());
        Assert::assertSame($mimeType, $fileInfo->getMimeType());
        Assert::assertSame('assetStorage', $fileInfo->getStorage());
        Assert::assertTrue($this->doesFileExist($fileInfo->getKey()));

        $variations = $reference->getVariations();
        Assert::assertCount(3, $variations);

        foreach ($variations as $variation) {
            $variationFileInfo = $variation->getFileInfo();
            Assert::assertNull($variationFileInfo);
        }

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
