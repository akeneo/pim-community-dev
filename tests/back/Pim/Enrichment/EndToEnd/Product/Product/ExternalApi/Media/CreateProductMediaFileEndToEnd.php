<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\Product\ExternalApi\Media;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class CreateProductMediaFileEndToEnd extends ApiTestCase
{
    private array $files = [];
    private ApiResourceRepositoryInterface $fileRepository;
    private FilesystemOperator $fileSystem;

    /**
     * @group critical
     */
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
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');
        $this->assertCount(2, $product->getValues());
        $this->assertSame('foo', $product->getIdentifier());

        $productValueFile = $product->getValues()->getByCodes('an_image');
        $this->assertInstanceOf(FileInfoInterface::class, $productValueFile->getData());
        $this->assertEquals($productValueFile->getData(), $fileInfo);

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
            "message": "The txt file extension is not allowed for the an_image attribute. Allowed extensions are jpg, gif, png.",
            "attribute": "an_image",
            "locale": null,
            "scope": null
        }
    ]
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testErrorWhenMimeTypeIsForbidden()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);
        $this->assertCount(0, $this->fileRepository->findAll());

        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'malicious_file.jpg';
        copy($this->getFixturePath('malicious_file.jpg'), $path);
        $file = new UploadedFile($path, 'malicious_file.jpg');
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
            "message": "The MIME type is not allowed for jpg (allowed types: image/jpeg, found text/plain).",
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
    "message": "The not_found attribute does not exist in your PIM."
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
    "message": "You should at least give one of the following properties: \"product\" or \"product_model\"."
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
        return $this->fileSystem->fileExists($pathFile);
    }

    /**
     * Remove all files generated by tests
     *
     * @param string $pathFile
     */
    protected function unlinkFile($pathFile)
    {
        if ($this->fileSystem->fileExists($pathFile)) {
            $this->fileSystem->delete($pathFile);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fileRepository = $this->get('pim_api.repository.media_file');

        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: 'foo',
            userIntents: []
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('foo');

        $this->get('akeneo_storage_utils.doctrine.object_detacher')->detach($product);

        $this->files['image'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.jpg';
        copy($this->getFixturePath('akeneo.jpg'), $this->files['image']);

        $this->files['file'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.txt';
        copy($this->getFixturePath('akeneo.txt'), $this->files['file']);

        $this->fileSystem = $this->get('akeneo_file_storage.file_storage.filesystem_provider')
                                 ->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        if (null === $id) {
            throw new \InvalidArgumentException(\sprintf('No user exists with username "%s"', $username));
        }

        return \intval($id);
    }
}
