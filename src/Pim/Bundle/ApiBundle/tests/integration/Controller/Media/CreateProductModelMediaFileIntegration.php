<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Media;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use League\Flysystem\FilesystemInterface;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
use Pim\Component\Catalog\FileStorage;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class CreateProductModelMediaFileIntegration extends ApiTestCase
{
    /** @var ApiResourceRepositoryInterface */
    private $fileRepository;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var array */
    private $files = [];

    /*** @var FilesystemInterface */
    private $fileSystem;

    public function testCreateAMediaFile()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product_model' => json_encode([
                'code' => 'product_model_image',
                'attribute' => 'an_image',
                'locale' => null,
                'scope' => null
            ]),
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
        $productModel = $this->productModelRepository->findOneByIdentifier('product_model_image');
        $this->assertCount(3, $productModel->getRawValues());

        $valueImage = $productModel->getValues()->getByCodes('an_image');
        $this->assertInstanceOf(FileInfoInterface::class, $valueImage->getData());
        $this->assertEquals($valueImage->getData(), $fileInfo);

        // check if file has been created on file system
        $this->assertTrue($this->doesFileExist($fileInfo->getKey()));

        // remove file from the file system
        $this->unlinkFile($fileInfo->getKey());
    }

    public function testFileDoesNotExist()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $content = [
            'product_model' => json_encode([
                'code' => 'product_model_image',
                'attribute' => 'an_image',
                'locale' => null,
                'scope' => null
            ]),
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, []);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Property \"file\" is required."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testOnlyOneTypeOfEntityIsUpdated()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product_model' => json_encode([
                'code' => 'product_model_image',
                'attribute' => 'an_image',
                'locale' => null,
                'scope' => null
            ]),
            'product' => json_encode([
                'identifier' => 'a_product',
                'attribute' => 'an_image',
                'locale' => null,
                'scope' => null
            ])
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "You should give either a \"product\" or a \"product_model\" key."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testInvalidJson()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product_model' => "\"code\":\"product_model_image\",\"attribute\":\"an_image\",\"locale\":null,\"scope\":null}"
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 400,
    "message": "Invalid json message received"
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testInvalidContent()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product_model' => json_encode([
                'code' => 'product_model_image',
                'locale' => null,
                'scope' => null
            ])
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Product model property must contain \"code\", \"attribute\", \"locale\" and \"scope\" properties."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testProductModelDoesNotExist()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product_model' => json_encode([
                'code' => 'unknown_product_model',
                'attribute' => 'an_image',
                'locale' => null,
                'scope' => null
            ])
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Product model \"unknown_product_model\" does not exist."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testAttributeNotInAttributeSet()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product_model' => json_encode([
                'code' => 'parent_product_model',
                'attribute' => 'an_image',
                'locale' => null,
                'scope' => null
            ])
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

        $expected = <<<JSON
{
    "code": 422,
    "message": "Validation failed.",
    "errors":[
        {
            "property":"attribute",
            "message":"Cannot set the property \"an_image\" to this entity as it is not in the attribute set"
        }
    ]
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testAttributeDoesNotExist()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product_model' => json_encode([
                'code' => 'product_model_image',
                'attribute' => 'an_image_unknown',
                'locale' => null,
                'scope' => null
            ])
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Property \"an_image_unknown\" does not exist."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testScopeDoesNotExist()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product_model' => json_encode([
                'code' => 'parent_product_model',
                'attribute' => 'a_localizable_scopable_image',
                'locale' => 'en_US',
                'scope' => 'Oumuamua'
            ])
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Attribute \"a_localizable_scopable_image\" expects an existing scope, \"Oumuamua\" given."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testLocaleDoesNotExist()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product_model' => json_encode([
                'code' => 'parent_product_model',
                'attribute' => 'a_localizable_scopable_image',
                'locale' => 'Esperanto',
                'scope' => 'tablet'
            ])
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Attribute \"a_localizable_scopable_image\" expects an existing and activated locale, \"Esperanto\" given."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testNoScopeGivenForScopableAttribute()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product_model' => json_encode([
                'code' => 'parent_product_model',
                'attribute' => 'a_localizable_scopable_image',
                'locale' => 'en_US',
                'scope' => null
            ])
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Attribute \"a_localizable_scopable_image\" expects a scope, none given."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testNoLocaleGivenForLocalizableAttribute()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $this->assertCount(0, $this->fileRepository->findAll());

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product_model' => json_encode([
                'code' => 'parent_product_model',
                'attribute' => 'a_localizable_scopable_image',
                'locale' => null,
                'scope' => 'tablet'
            ])
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Attribute \"a_localizable_scopable_image\" expects a locale, none given."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testScopeGivenButAttributeNotScopable()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $content = [
            'product_model' => json_encode([
                'code' => 'product_model_image',
                'attribute' => 'an_image',
                'locale' => null,
                'scope' => 'tablet'
            ]),
        ];
        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Attribute \"an_image\" does not expect a scope, \"tablet\" given."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testLocaleGivenButAttributeNotLocalizable()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $content = [
            'product_model' => json_encode([
                'code' => 'product_model_image',
                'attribute' => 'an_image',
                'locale' => 'en_US',
                'scope' => null
            ]),
        ];
        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Attribute \"an_image\" does not expect a locale, \"en_US\" given."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testAttributeNotInFamily()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data']);

        $content = [
            'product_model' => json_encode([
                'code' => 'product_model_image',
                'attribute' => 'a_scopable_image',
                'locale' => null,
                'scope' => 'tablet'
            ]),
        ];
        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 422,
    "message": "Validation failed.",
    "errors": [
        {
            "property": "attribute",
            "message": "Attribute \"a_scopable_image\" does not belong to the family \"familyA\""
        }
    ]
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

        $familyA = $this->getFromTestContainer('pim_catalog.repository.family')->findOneByIdentifier('familyA');
        $this->getFromTestContainer('pim_catalog.updater.family')->update(
            $familyA,
            [
                'attributes' => [
                    'a_date',
                    'a_file',
                    'a_localizable_image',
                    'a_localized_and_scopable_text_area',
                    'a_metric',
                    'a_multi_select',
                    'a_number_float',
                    'a_number_float_negative',
                    'a_number_integer',
                    'a_price',
                    'a_ref_data_multi_select',
                    'a_ref_data_simple_select',
                    'a_scopable_price',
                    'a_simple_select',
                    'a_text',
                    'a_text_area',
                    'a_yes_no',
                    'an_image',
                    'a_localizable_scopable_image'
            ]
        ]);
        $this->assertCount(0, $this->getFromTestContainer('validator')->validate($familyA));
        $this->getFromTestContainer('pim_catalog.saver.family')->save($familyA);

        $familyVariant = $this->getFromTestContainer('pim_catalog.factory.family_variant')->create();
        $this->getFromTestContainer('pim_catalog.updater.family_variant')
            ->update(
                $familyVariant,
                [
                    'code' => 'fam_var_with_image',
                    'labels' => [
                        'fr_FR' => 'avec image',
                        'en_US' => 'with image'
                    ],
                    'family' => 'familyA',
                    'variant_attribute_sets' => [
                        [
                            'level'      => 1,
                            'axes'       => ['a_simple_select'],
                            'attributes' => [
                                'a_simple_select',
                                'a_number_float',
                                'an_image'
                            ],
                        ],
                        [
                            'level'      => 2,
                            'axes'       => ['a_yes_no'],
                            'attributes' => ['a_yes_no', 'sku'],
                        ]
                    ],
                ]
            );
        $this->assertCount(0, $this->getFromTestContainer('validator')->validate($familyVariant));
        $this->getFromTestContainer('pim_catalog.saver.family_variant')->save($familyVariant);

        $productModelParent = $this->getFromTestContainer('pim_catalog.factory.product_model')->create();
        $productModel = $this->getFromTestContainer('pim_catalog.factory.product_model')->create();

        $this->getFromTestContainer('pim_catalog.updater.product_model')
            ->update(
                $productModelParent,
                [
                    'code' => 'parent_product_model',
                    'family_variant' => 'fam_var_with_image',
                    'values' => []
                ]
            );
        $this->assertCount(0, $this->getFromTestContainer('pim_catalog.validator.product_model')->validate($productModelParent));
        $this->getFromTestContainer('pim_catalog.saver.product_model')->save($productModelParent);

        $this->getFromTestContainer('pim_catalog.updater.product_model')
            ->update(
                $productModel,
                [
                    'code' => 'product_model_image',
                    'parent' => 'parent_product_model',
                    'family_variant' => 'fam_var_with_image',
                    'values' => [
                        'a_number_float' => [['data' => '12.5', 'locale' => null, 'scope' => null]],
                        'a_simple_select' => [['data' => 'optionA', 'locale' => null, 'scope' => null]]
                    ]
                ]
            );
        $this->assertCount(0, $this->getFromTestContainer('pim_catalog.validator.product_model')->validate($productModel));
        $this->getFromTestContainer('pim_catalog.saver.product_model')->save($productModel);

        $this->getFromTestContainer('akeneo_elasticsearch.client.product_model')->refreshIndex();
        $this->getFromTestContainer('akeneo_elasticsearch.client.product')->refreshIndex();

        $this->fileRepository = $this->getFromTestContainer('pim_api.repository.media_file');
        $this->productModelRepository = $this->getFromTestContainer('pim_catalog.repository.product_model');

        $this->files['image'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.jpg';
        copy($this->getFixturePath('akeneo.jpg'), $this->files['image']);

        $mountManager = $this->getFromTestContainer('oneup_flysystem.mount_manager');
        $this->fileSystem = $mountManager->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS);

        $this->getFromTestContainer('doctrine.orm.default_entity_manager')->clear();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
