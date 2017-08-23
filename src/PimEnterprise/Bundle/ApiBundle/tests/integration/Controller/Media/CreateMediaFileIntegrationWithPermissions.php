<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Media;

use League\Flysystem\FilesystemInterface;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
use Pim\Component\Api\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\FileStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class CreateMediaFileIntegrationWithPermissions extends AbstractMediaFileTestCase
{
    /** @var array */
    private $files = [];

    /** @var ApiResourceRepositoryInterface */
    private $fileRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /*** @var FilesystemInterface */
    private $fileSystem;

    public function testErrorWhenProductNotViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data'], null, null, 'mary', 'mary');
        $this->assertCount(3, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['file'], 'akeneo.txt');
        $content = [
            'product' => '{"identifier":"product_not_viewable_by_redactor", "attribute":"an_image", "locale":null, "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();
        $this->assertCount(3, $this->fileRepository->findAll());

        $expected = <<<JSON
{
    "code": 403,
    "message": "You can neither view, nor update, nor delete the product \"product_not_viewable_by_redactor\", as it is only categorized in categories on which you do not have a view permission."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testErrorWhenAttributeGroupIsOnlyViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data'], null, null, 'mary', 'mary');
        $this->assertCount(3, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['file'], 'akeneo.txt');
        $content = [
            'product' => '{"identifier":"product_viewable_by_everybody_1", "attribute":"a_localizable_image", "locale":"en_US", "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();
        $this->assertCount(4, $this->fileRepository->findAll());

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 403,
    "message": "Attribute \"a_localizable_image\" belongs to the attribute group \"attributeGroupB\" on which you only have view permission."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testErrorWhenRedactorDoesNotHaveAnyRightsOnAttributeGroup()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data'], null, null, 'mary', 'mary');
        $this->assertCount(3, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['file'], 'akeneo.txt');
        $content = [
            'product' => '{"identifier":"product_viewable_by_everybody_1", "attribute":"a_multi_select", "locale":"en_US", "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();
        $this->assertCount(3, $this->fileRepository->findAll());

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Property \"a_multi_select\" does not exist."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testErrorWhenProductIsOnlyViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data'], null, null, 'mary', 'mary');
        $this->assertCount(3, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['file'], 'akeneo.png');
        $content = [
            'product' => '{"identifier":"product_viewable_by_everybody_1", "attribute":"an_image", "locale":null, "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();
//        var_dump($response->getContent());die();
        $this->assertCount(4, $this->fileRepository->findAll());

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 403,
    "message": "Product \"product_viewable_by_everybody_1\" cannot be updated. It should be at least in an own category."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->fileRepository = $this->get('pim_api.repository.media_file');
        $this->productRepository = $this->get('pim_api.repository.product');

        $product = $this->get('pim_catalog.builder.product')->createProduct('foo');
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_storage_utils.doctrine.object_detacher')->detach($product);

        $this->files['image'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.jpg';
        copy($this->getFixturePath('akeneo.jpg'), $this->files['image']);

        $this->files['file'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.txt';
        copy($this->getFixturePath('akeneo.txt'), $this->files['file']);

        $mountManager = $this->get('oneup_flysystem.mount_manager');
        $this->fileSystem = $mountManager->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS);
    }
}
