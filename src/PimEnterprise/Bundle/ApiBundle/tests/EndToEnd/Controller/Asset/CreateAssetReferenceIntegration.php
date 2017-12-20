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
        $this->assertCorrectlyUpsertAssetReference(
            $this->files['ziggy'],
            'new_ziggy.png',
            'image/png',
            'localizable_asset',
            'en_US',
            201
        );

        $this->assertGenerateVariations('localizable_asset', 'en_US', 'image/png', 3);
    }

    public function testUpdateAReferenceFileOnNotLocalizableAsset()
    {
        $this->assertCorrectlyUpsertAssetReference(
            $this->files['ziggy'],
            'new_ziggy.png',
            'image/png',
            'non_localizable_asset',
            'no-locale',
            201
        );

        $this->assertGenerateVariations('non_localizable_asset', 'no-locale', 'image/png', 3);
    }

    /**
     * Should be an integration test.
     */
    public function testCreateAReferenceFileWithFailingVariations()
    {
        $expectedContent = <<<JSON
{
	"message": "Some variation files were not generated properly.",
	"errors": [
	    {
		    "message": "Impossible to \"scale\" the image \"/tmp/pim/file_storage/7/a/1/b/akeneo-en_US-ecommerce.png\" with a width bigger than the original.",
		    "scope": "ecommerce",
		    "locale": "en_US"
	    },
	    {
		    "message": "Impossible to \"scale\" the image \"/tmp/pim/file_storage/7/a/1/b/akeneo-en_US-ecommerce_china.png\" with a width bigger than the original.",
		    "scope": "ecommerce_china",
		    "locale": "en_US"
	    }
    ]
}
JSON;

        $this->assertCorrectlyUpsertAssetReference(
            $this->files['akeneo'],
            'akeneo.jpg',
            'image/jpeg',
            'localizable_asset_without_references',
            'en_US',
            201,
            $expectedContent
        );

        $ecommerce = $this->get('pim_api.repository.channel')->findOneByIdentifier('ecommerce');
        $ecommerceChina = $this->get('pim_api.repository.channel')->findOneByIdentifier('ecommerce_china');
        $tablet = $this->get('pim_api.repository.channel')->findOneByIdentifier('tablet');

        $locale = $this->get('pim_api.repository.locale')->findOneByIdentifier('en_US');
        $asset = $this->get('pimee_api.repository.asset')->findOneByIdentifier('localizable_asset_without_references');
        $reference = $asset->getReference($locale);

        Assert::assertNull($reference->getVariation($ecommerce)->getFileInfo());
        Assert::assertNull($reference->getVariation($ecommerceChina)->getFileInfo());
        Assert::assertNotNull($reference->getVariation($tablet)->getFileInfo());
    }

    /**
     * Should be an integration test.
     */
    public function testItDoesNotGenerateLockedVariations()
    {
        $asset = $this->get('pimee_api.repository.asset')->findOneByIdentifier('localizable_asset');
        $ecommerce = $this->get('pim_api.repository.channel')->findOneByIdentifier('ecommerce');
        $locale = $this->get('pim_api.repository.locale')->findOneByIdentifier('en_US');

        $variation = $asset->getReference($locale)->getVariation($ecommerce);
        $variation->setLocked(true);
        $key = $variation->getFileInfo()->getKey();

        Assert::assertCount(0, $this->get('validator')->validate($asset));
        $this->get('pimee_product_asset.saver.asset')->save($asset);

        $this->assertCorrectlyUpsertAssetReference(
            $this->files['ziggy'],
            'new_ziggy.png',
            'image/png',
            'localizable_asset',
            'en_US',
            201
        );

        $this->get('doctrine.orm.default_entity_manager')->clear();

        Assert::assertSame($key, $asset->getReference($locale)->getVariation($ecommerce)->getFileInfo()->getKey());
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
            'api/rest/v1/assets/localizable_asset/reference-files/no-locale',
            422,
            'The asset \"localizable_asset\" is localizable, you must provide an existing locale code. \"no-locale\" is only allowed when the asset is not localizable.'
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
            'The asset \"non_localizable_asset\" is not localizable, you must provide the string \"no-locale\" as a locale.'
        );
    }

    /**
     * Should be an integration test.
     */
    public function testErrorMessageWhenCreatingReferenceWithoutFile()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $client->request('POST', 'api/rest/v1/assets/localizable_asset/reference-files/en_US');
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
     * @param string $expectedBody
     */
    private function assertCorrectlyUpsertAssetReference(
        string $filePath,
        string $fileName,
        string $mimeType,
        string $assetCode,
        string $localeCode,
        int $status,
        string $expectedBody = ''
    ): void {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $file = new UploadedFile($filePath, $fileName);

        $client->request('POST', sprintf('api/rest/v1/assets/%s/reference-files/%s', $assetCode, $localeCode), [], ['file' => $file]);
        $response = $client->getResponse();

        Assert::assertSame($status, $response->getStatusCode());
        '' === $expectedBody ?
            Assert::assertSame($expectedBody, $response->getContent()):
            Assert::assertJsonStringEqualsJsonString($this->sanitize($expectedBody), $this->sanitize($response->getContent()));

        Assert::assertArrayHasKey('location', $response->headers->all());
        Assert::assertSame(
            sprintf('http://localhost/api/rest/v1/assets/%s/reference-files/%s', $assetCode, $localeCode),
            $response->headers->get('location')
        );

        $this->get('doctrine.orm.default_entity_manager')->clear();

        $locale = $this->get('pim_api.repository.locale')->findOneByIdentifier($localeCode);
        $asset = $this->get('pimee_api.repository.asset')->findOneByIdentifier($assetCode);
        $reference = $asset->getReference($locale);
        $referenceFileInfo = $reference->getFileInfo();

        Assert::assertNotNull($referenceFileInfo);
        Assert::assertSame($fileName, $referenceFileInfo->getOriginalFilename());
        Assert::assertSame($mimeType, $referenceFileInfo->getMimeType());
        Assert::assertSame('assetStorage', $referenceFileInfo->getStorage());
        Assert::assertTrue($this->doesFileExist($referenceFileInfo->getKey()));
    }

    /**
     * @param string $assetCode
     * @param string $localeCode
     * @param string $mimeType
     * @param int    $expectedNumberVariations
     */
    private function assertGenerateVariations(
        string $assetCode,
        string $localeCode,
        string $mimeType,
        int $expectedNumberVariations
    ):void {
        $locale = $this->get('pim_api.repository.locale')->findOneByIdentifier($localeCode);
        $asset = $this->get('pimee_api.repository.asset')->findOneByIdentifier($assetCode);
        $reference = $asset->getReference($locale);
        $referenceFileInfo = $reference->getFileInfo();

        $variations = $reference->getVariations();
        Assert::assertCount($expectedNumberVariations, $variations);

        foreach ($variations as $variation) {
            $variationFileInfo = $variation->getFileInfo();
            $variationSourceFileInfo = $variation->getSourceFileInfo();
            Assert::assertNotNull($variationFileInfo);
            Assert::assertNotNull($variationFileInfo->getKey());
            Assert::assertSame($referenceFileInfo->getOriginalFilename(), $variationSourceFileInfo->getOriginalFileName());
            Assert::assertSame($mimeType, $variationFileInfo->getMimeType());
        }
    }

    /**
     * @param string $url
     * @param int    $errorCode
     * @param string $message
     */
    private function assertError(string $url, int $errorCode, string $message): void
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $file = new UploadedFile($this->files['ziggy'], 'ziggy.png');

        $client->request('POST', $url, [], ['file' => $file]);
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

    private function sanitize(string $data): string
    {
        $data = preg_replace('#u0022#', '"', $data);

        return preg_replace('#"\\\\?/.*?"#', '"foo\\"', $data);
    }
}
