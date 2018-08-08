<?php

namespace Akeneo\Pim\Permission\Bundle\tests\EndToEnd\Asset;

use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Asset\Component\FileStorage;
use Akeneo\Asset\Component\Model\AssetInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetVariationAuthorizationIntegration extends ApiTestCase
{
    /**
     * Should be an integration test.
     */
    public function testOverallAccessDenied()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'kevin', 'kevin');

        $client->request('GET', '/api/rest/v1/assets/cat/variation-files/ecommerce/en_US');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "You are not allowed to access the web API."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testAccessGrantedForGettingAnAssetVariation()
    {
        $this->createAsset(['code' => 'an_asset', 'localizable' => false]);

        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/rest/v1/assets/an_asset/variation-files/ecommerce/no-locale');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * Should be an integration test.
     */
    public function testAccessGrantedForDownloadingAnAssetVariationFile()
    {
        $this->createAsset(['code' => 'an_asset', 'localizable' => false]);

        $client = $this->createAuthenticatedClient();

        //  The file is streamed so the console output is not polluted by it.
        $contentFile = '';
        ob_start(function ($streamedFile) use (&$contentFile) {
            $contentFile .= $streamedFile;

            return '';
        });
        $client->request('GET', '/api/rest/v1/assets/an_asset/variation-files/ecommerce/no-locale/download');
        ob_end_clean();

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * Should be an integration test.
     */
    public function testAccessGrantedForCreatingAnAssetVariation()
    {
        $this->createAsset(['code' => 'an_asset', 'localizable' => false]);
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.jpg';
        copy($this->getFixturePath('akeneo.jpg'), $filePath);

        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $file = new UploadedFile($filePath, 'akeneo.jpg');

        $client->request('POST', '/api/rest/v1/assets/an_asset/variation-files/ecommerce/no-locale', [], ['file' => $file]);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }

    /**
     * Should be an integration test.
     */
    public function testAccessDeniedForGettingAnAssetVariation()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/assets/cat/variation-files/ecommerce/en_US');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to list asset variations."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testAccessDeniedForDownloadingAnAssetVariationFile()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');

        $client->request('GET', '/api/rest/v1/assets/cat/variation-files/ecommerce/en_US/download');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to list asset variations."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    /**
     * Should be an integration test.
     */
    public function testAccessDeniedForCreatingAnAssetVariation()
    {
        $this->createAsset(['code' => 'an_asset', 'localizable' => false]);
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.jpg';
        copy($this->getFixturePath('akeneo.jpg'), $filePath);

        $client = $this->createAuthenticatedClient(
            [],
            ['CONTENT_TYPE' => 'multipart/form-data'],
            null,
            null,
            'julia',
            'julia'
        );

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to create or update asset variations."
}
JSON;

        $file = new UploadedFile($filePath, 'akeneo.jpg');

        $client->request('POST', '/api/rest/v1/assets/an_asset/variation-files/ecommerce/no-locale', [], ['file' => $file]);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * Creates an asset with data.
     *
     * @param array $data
     *
     * @throws \Exception
     *
     * @return AssetInterface
     */
    private function createAsset(array $data): AssetInterface
    {
        $asset = $this->get('pimee_product_asset.factory.asset')->create();

        $this->get('pimee_product_asset.updater.asset')->update($asset, $data);

        foreach ($asset->getReferences() as $reference) {
            $fileInfo = new \SplFileInfo($this->getFixturePath('ziggy.png'));
            $storedFile = $this->get('akeneo_file_storage.file_storage.file.file_storer')->store(
                $fileInfo,
                FileStorage::ASSET_STORAGE_ALIAS
            );

            $reference->setFileInfo($storedFile);
            $this->get('pimee_product_asset.updater.files')->resetAllVariationsFiles($reference, true);
        }

        $errors = $this->get('validator')->validate($asset);
        $this->assertCount(0, $errors);

        $this->get('pimee_product_asset.saver.asset')->save($asset);

        $this->get('pimee_product_asset.variations_collection_files_generator')->generate(
            $asset->getVariations(),
            true
        );

        return $asset;
    }
}
