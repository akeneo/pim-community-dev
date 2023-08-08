<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Media;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class CreateMediaFileWithPermissionsEndToEnd extends AbstractMediaFileTestCase
{
    private array $files = [];
    private ApiResourceRepositoryInterface $fileRepository;

    public function testCreateAMediaFile()
    {
        $this->loginAs('admin');

        $product = $this->createProduct('product_editable_by_manager', [
            new SetCategories(['categoryA']),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce', 'en_US', 'EN ecommerce'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'fr_FR', 'FR ecommerce'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'de_DE', 'DE ecommerce'),
            new SetNumberValue('a_number_float', null, null, '12.05'),
            new SetImageValue('a_localizable_image', null, 'en_US', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
            new SetImageValue('a_localizable_image', null, 'fr_FR', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
            new SetImageValue('a_localizable_image', null, 'de_DE', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
            new SetMeasurementValue('a_metric_without_decimal_negative', null, null, -10, 'CELSIUS'),
            new SetMultiSelectValue('a_multi_select', null, null, ['optionA', 'optionB']),
        ]);

        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data'], null, null, 'mary', 'mary');

        $this->assertCount(6, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product' => '{"identifier":"product_editable_by_manager", "attribute":"an_image", "locale":null, "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        // test response
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEmpty($response->getContent());

        // check in repo if file has been created
        $fileInfos = $this->fileRepository->findAll();
        $this->assertCount(7, $fileInfos);

        // check the content of file db
        $fileInfo = current($fileInfos);
        $this->assertSame('akeneo.jpg', $fileInfo->getOriginalFilename());
        $this->assertSame('image/jpeg', $fileInfo->getMimeType());
        $this->assertSame('catalogStorage', $fileInfo->getStorage());

        // check if product value has been created
        $productDraft = $this->get('pimee_workflow.repository.product_draft')->findByEntityWithValues($product);
        $this->assertStringContainsString('akeneo.jpg', $productDraft[0]->getChange('an_image', null, null));
    }

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
    "code": 404,
    "message": "Product \"product_not_viewable_by_redactor\" does not exist or you do not have permission to access it."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testErrorWhenAttributeGroupIsOnlyViewableByRedactor()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data'], null, null, 'mary', 'mary');
        $this->assertCount(3, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['file'], 'akeneo.txt');
        $content = [
            'product' => '{"identifier":"product_without_category", "attribute":"a_localizable_image", "locale":"en_US", "scope":null}',
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
            'product' => '{"identifier":"product_without_category", "attribute":"a_multi_select", "locale":"en_US", "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();
        $this->assertCount(3, $this->fileRepository->findAll());

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "The a_multi_select attribute does not exist in your PIM."
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
        $this->assertCount(4, $this->fileRepository->findAll());

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 403,
    "message": "Product \"product_viewable_by_everybody_1\" cannot be updated. You only have a view right on this product."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fileRepository = $this->get('pim_api.repository.media_file');
        $product = $this->get('pim_catalog.builder.product')->createProduct('foo');
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_storage_utils.doctrine.object_detacher')->detach($product);

        $this->files['image'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.jpg';
        copy($this->getFixturePath('akeneo.jpg'), $this->files['image']);

        $this->files['file'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.txt';
        copy($this->getFixturePath('akeneo.txt'), $this->files['file']);
    }
}
