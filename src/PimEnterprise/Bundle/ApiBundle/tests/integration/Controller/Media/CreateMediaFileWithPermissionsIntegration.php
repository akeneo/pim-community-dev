<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Media;

use League\Flysystem\FilesystemInterface;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
use Pim\Component\Api\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\FileStorage;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class CreateMediaFileWithPermissionsIntegration extends AbstractMediaFileTestCase
{
    /** @var array */
    private $files = [];

    /** @var ApiResourceRepositoryInterface */
    private $fileRepository;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductDraftRepositoryInterface */
    private $productDraftRepository;

    /*** @var FilesystemInterface */
    private $fileSystem;

    public function testCreateAMediaFile()
    {
        $product = $this->createProduct('product_editable_by_manager', [
            'categories' => ['categoryA'],
            'values'     => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'EN ecommerce', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'FR ecommerce', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'DE ecommerce', 'locale' => 'de_DE', 'scope' => 'ecommerce']
                ],
                'a_number_float' => [['data' => '12.05', 'locale' => null, 'scope' => null]],
                'a_localizable_image' => [
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'en_US', 'scope' => null],
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'fr_FR', 'scope' => null],
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'de_DE', 'scope' => null]
                ],
                'a_metric_without_decimal_negative' => [
                    ['data' => ['amount' => -10, 'unit' => 'CELSIUS'], 'locale' => null, 'scope' => null]
                ],
                'a_multi_select' => [
                    ['locale' => null, 'scope' => null, 'data' => ['optionA', 'optionB']],
                ]
            ]
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
        $productDraft = $this->testKernel->getContainer()->get('pimee_workflow.repository.product_draft')->findByProduct($product);
        $this->assertContains('akeneo.jpg', $productDraft[0]->getChange('an_image', null, null));
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
        $this->productDraftRepository = $this->get('pimee_workflow.repository.product_draft');

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
