<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Media;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Test\Integration\Configuration;
use League\Flysystem\FilesystemInterface;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
use Pim\Component\Api\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\FileStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class CreateMediaFileIntegration extends ApiTestCase
{
    /** @var array */
    private $files = [];

    /** @var ApiResourceRepositoryInterface */
    private $fileRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /*** @var FilesystemInterface */
    private $fileSystem;

    public function testCreateAMediaFile()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product' => '{"identifier":"foo", "attribute":"an_image", "locale":null, "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        // test response
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEmpty($response->getContent());

        // check in repo if file has been created
        $fileInfos = $this->fileRepository->findAll();
        $this->assertCount(1, $fileInfos);

        // check the content of file db
        $fileInfo = current($fileInfos);
        $this->assertSame('akeneo.jpg', $fileInfo->getOriginalFilename());
        $this->assertSame('image/jpeg', $fileInfo->getMimeType());
        $this->assertSame('catalogStorage', $fileInfo->getStorage());

        // check if product value has been created
        $product = $this->productRepository->findOneByIdentifier('foo');
        $this->assertCount(2, $product->getValues());
        $this->assertSame('foo', $product->getValues()->first()->getData());

        $productValueFile = $product->getValues()->next();
        $this->assertInstanceOf(FileInfoInterface::class, $productValueFile->getData());
        $this->assertSame($productValueFile->getData(), $fileInfo);

        // check if file has been created on file system
        $this->assertTrue($this->doesFileExist($fileInfo->getKey()));

        // remove file from the file system
        $this->unlinkFile($fileInfo->getKey());
    }

    public function testErrorWhenExtensionIsForbidden()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);
        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['file'], 'akeneo.txt');
        $content = [
            'product' => '{"identifier":"foo", "attribute":"an_image", "locale":null, "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertCount(0, $this->fileRepository->findAll());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Validation failed.",
    "errors": [
        {
            "property": "values",
            "message": "The file extension is not allowed (allowed extensions: jpg, gif, png).",
            "attribute": "an_image",
            "locale": null,
            "scope": null
        }
    ]
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testErrorWhenProductDoesNotExist()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);
        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['file'], 'akeneo.txt');
        $content = [
            'product' => '{"identifier":"not_found", "attribute":"an_image", "locale":null, "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();
        $this->assertCount(0, $this->fileRepository->findAll());

        $expected = <<<JSON
{
    "code": 422,
    "message": "Product \"not_found\" does not exist."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testErrorWhenAttributeDoesNotExist()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);
        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['file'], 'akeneo.txt');
        $content = [
            'product' => '{"identifier":"foo", "attribute":"not_found", "locale":null, "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();
        $this->assertCount(0, $this->fileRepository->findAll());

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Property \"not_found\" does not exist."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testErrorWhenPropertiesAreMissing()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);
        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['file'], 'akeneo.txt');
        $content = [
            'product' => '{"identifier":"foo", "locale":null, "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();
        $this->assertCount(0, $this->fileRepository->findAll());

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Product property must contain \"identifier\", \"attribute\", \"locale\" and \"scope\" properties."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testErrorWhenProductPropertyIsMissing()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);
        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['file'], 'akeneo.txt');
        $content = [
            'products' => '{"identifier":"foo", "locale":null, "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();
        $this->assertCount(0, $this->fileRepository->findAll());

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Property \"product\" is required."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testErrorWhenProductFileIsMissing()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);
        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['file'], 'akeneo.txt');
        $content = [
            'product' => '{"identifier":"foo", "attribute":"a_file","locale":null, "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['image' => $file]);
        $response = $client->getResponse();
        $this->assertCount(0, $this->fileRepository->findAll());

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Property \"file\" is required."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testErrorWhenProductContentIsInvalid()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);
        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['file'], 'akeneo.txt');
        $content = [
            'product' => '{"identifier}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['image' => $file]);
        $response = $client->getResponse();
        $this->assertCount(0, $this->fileRepository->findAll());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 400,
    "message": "Invalid json message received"
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * Remove all files generated by tests
     *
     * @param string $pathFile
     *
     * @return bool
     */
    protected function doesFileExist($pathFile)
    {
        return $this->fileSystem->has($pathFile);
    }

    /**
     * Remove all files generated by tests
     *
     * @param string $pathFile
     */
    protected function unlinkFile($pathFile)
    {
        if ($this->fileSystem->has($pathFile)) {
            $this->fileSystem->delete($pathFile);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->fileRepository = $this->get('pim_api.repository.media_file');
        $this->productRepository = $this->get('pim_api.repository.product');

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $product = $this->get('pim_catalog.builder.product')->createProduct('foo');
            $this->get('pim_catalog.saver.product')->save($product);
            $this->get('akeneo_storage_utils.doctrine.object_detacher')->detach($product);
        }

        $this->files['image'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.jpg';
        copy($this->getFixturePath('akeneo.jpg'), $this->files['image']);

        $this->files['file'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.txt';
        copy($this->getFixturePath('akeneo.txt'), $this->files['file']);

        $mountManager = $this->get('oneup_flysystem.mount_manager');
        $this->fileSystem = $mountManager->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS);
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            true
        );
    }
}
