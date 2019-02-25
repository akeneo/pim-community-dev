<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\ProductModel;

use Akeneo\Test\Integration\Configuration;
use PHPUnit\Framework\Assert;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class AssociateMediaToProductModelWithPermissionEndToEnd extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    /** @var array */
    private $files = [];

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loader = new PermissionFixturesLoader($this->testKernel->getContainer());
        $this->files['image'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.jpg';
        copy($this->getFixturePath('akeneo.jpg'), $this->files['image']);

        $this->files['file'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.txt';
        copy($this->getFixturePath('akeneo.txt'), $this->files['file']);
    }

    public function testCantAssociateMediaToANotViewableLocaleAttributeMediaOfAProductModel()
    {
        $this->loadProductModelsFixturesForAttributeAndLocaleAndMediaPermissions();

        $this->assertCount(0, $this->getFromTestContainer('pim_api.repository.media_file')->findAll());

        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data'], null, null, 'mary', 'mary');

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product_model' => '{"code":"root_product_model", "attribute":"root_product_model_no_view_attribute_media", "locale":null, "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $expected = <<<JSON
{
    "code": 422,
    "message": "Property \"root_product_model_no_view_attribute_media\" does not exist."
}
JSON;
        $this->getFromTestContainer('doctrine')->getManager()->clear();
        $this->assertCount(0, $this->getFromTestContainer('pim_api.repository.media_file')->findAll());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());

    }


    public function testCantAssociateMediaToAnOnlyViewableLocaleAttributeMediaOfAProductModel()
    {
        $this->loadProductModelsFixturesForAttributeAndLocaleAndMediaPermissions();

        $this->assertCount(0, $this->getFromTestContainer('pim_api.repository.media_file')->findAll());

        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data'], null, null, 'mary', 'mary');

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product_model' => '{"code":"root_product_model", "attribute":"root_product_model_view_attribute_media", "locale":"fr_FR", "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $expected = <<<JSON
{
    "code": 403,
    "message": "Attribute \"root_product_model_view_attribute_media\" belongs to the attribute group \"attributeGroupB\" on which you only have view permission."
}
JSON;
        $this->getFromTestContainer('doctrine')->getManager()->clear();
        $this->assertCount(1, $this->getFromTestContainer('pim_api.repository.media_file')->findAll());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());

    }

    public function testHaveRightToAssociateMediaToAViewableAttributeMediaOfAProductModel()
    {
        $this->loadProductModelsFixturesForAttributeAndLocaleAndMediaPermissions();

        $this->assertCount(0, $this->getFromTestContainer('pim_api.repository.media_file')->findAll());

        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data'], null, null, 'mary', 'mary');

        $file = new UploadedFile($this->files['image'], 'akeneo.jpg');
        $content = [
            'product_model' => '{"code":"root_product_model", "attribute":"root_product_model_edit_attribute_media", "locale":"en_US", "scope":null}',
        ];

        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();


        // test response
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEmpty($response->getContent());

        // check in repo if file has been created
        $fileInfos = $this->getFromTestContainer('pim_api.repository.media_file')->findAll();

        $this->assertCount(1, $fileInfos);

        // check the content of file db
        $fileInfo = current($fileInfos);
        $this->assertSame('akeneo.jpg', $fileInfo->getOriginalFilename());
        $this->assertSame('image/jpeg', $fileInfo->getMimeType());
        $this->assertSame('catalogStorage', $fileInfo->getStorage());

        // check if product model value has been created
        $this->getFromTestContainer('doctrine')->getManager()->clear();
        $productModel = $this->getFromTestContainer('pim_catalog.repository.product_model')->findOneByCode('root_product_model');
        $attributeCodes = $productModel->getUsedAttributeCodes();
        $mediaAttributeCode = current(array_filter($attributeCodes, function($attributeCode){
            return $attributeCode == 'root_product_model_edit_attribute_media';
        }));

        $property = $productModel->getValues()->getByCodes('root_product_model_edit_attribute_media', null, 'en_US');
        $this->assertEquals('root_product_model_edit_attribute_media', $mediaAttributeCode);
        $this->assertEquals($fileInfo->getOriginalFilename(), $property->getData()->getOriginalFilename());

    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function loadProductModelsFixturesForAttributeAndLocaleAndMediaPermissions(): void
    {
        $this->loader->loadProductModelsFixturesForAttributeAndLocalePermissions();

        $this->loader->createAttribute('root_product_model_no_view_attribute_media', 'none', false, AttributeTypes::FILE);
        $this->loader->createAttribute('root_product_model_view_attribute_media', 'view', true, AttributeTypes::FILE);
        $this->loader->createAttribute('root_product_model_edit_attribute_media', 'edit', true, AttributeTypes::FILE);
        $this->loader->createAttribute('sub_product_model_no_view_attribute_media', 'none', false, AttributeTypes::FILE);
        $this->loader->createAttribute('sub_product_model_view_attribute_media', 'view', true, AttributeTypes::FILE);
        $this->loader->createAttribute('sub_product_model_edit_attribute_media', 'edit', true, AttributeTypes::FILE);

        $family = $this->getFromTestContainer('pim_catalog.repository.family')->findOneByIdentifier('family_permission');
        $this->getFromTestContainer('pim_catalog.updater.family')->update($family, [
            'attributes'  => [
                'root_product_model_no_view_attribute',
                'root_product_model_view_attribute',
                'root_product_model_edit_attribute',
                'sub_product_model_no_view_attribute',
                'sub_product_model_view_attribute',
                'sub_product_model_edit_attribute',
                'sub_product_model_axis_attribute',
                'variant_product_no_view_attribute',
                'variant_product_view_attribute',
                'variant_product_edit_attribute',
                'variant_product_axis_attribute',
                'root_product_model_no_view_attribute_media',
                'root_product_model_view_attribute_media',
                'root_product_model_edit_attribute_media',
                'sub_product_model_no_view_attribute_media',
                'sub_product_model_view_attribute_media',
                'sub_product_model_edit_attribute_media'
            ]
        ]);

        $errors = $this->getFromTestContainer('validator')->validate($family);
        Assert::assertCount(0, $errors);
        $this->getFromTestContainer('pim_catalog.saver.family')->save($family);


        $familyVariant = $this->getFromTestContainer('pim_catalog.repository.family_variant')->findOneByIdentifier('family_variant_permission');
        $this->getFromTestContainer('pim_catalog.updater.family_variant')->update($familyVariant, [
            'variant_attribute_sets' => [
                [
                    'axes' => ['sub_product_model_axis_attribute'],
                    'attributes' => [
                        'sub_product_model_no_view_attribute',
                        'sub_product_model_no_view_attribute_media',
                        'sub_product_model_view_attribute_media',
                        'sub_product_model_edit_attribute_media'
                    ],
                    'level'=> 1,
                ],
                [
                    'axes' => ['variant_product_axis_attribute'],
                    'attributes' => [
                        'variant_product_no_view_attribute'
                    ],
                    'level'=> 2,
                ]
            ],
        ]);

        $errors = $this->getFromTestContainer('validator')->validate($familyVariant);
        Assert::assertCount(0, $errors);
        $this->getFromTestContainer('pim_catalog.saver.family_variant')->save($familyVariant);
    }
}
