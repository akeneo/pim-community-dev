<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\Update;

use Pim\Component\Catalog\FileStorage;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\AbstractProductTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class UpdateProductDraftIntegration extends AbstractProductTestCase
{
    public function setUp()
    {
        parent::setUp();

        $product = $this->createProduct('product_draft_for_redactor', [
            'categories' => ['categoryA'],
            'values'     => [
                'a_text' => [
                    ['data' => 'a text', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
        $this->createProductDraft('mary', $product, [
            'values' => [
                'a_simple_select' => [
                    ['data' => 'optionA', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
    }

    public function testErrorWhenFieldsAreUpdatedOnUpdateADraft()
    {
        $data = <<<JSON
{
    "enabled": false,
    "groups": ["groupA"]
}
JSON;
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/product_draft_for_redactor', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $expected = <<<JSON
{
    "code": 403,
    "message": "You cannot update the following fields \"enabled, groups\". You should at least own this product to do it."
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testSuccessfulToUpdateADraft()
    {
        $data = <<<JSON
{
    "values": {
        "a_text": [
            { "data": "the text", "locale": null, "scope": null }
        ]
    }
}
JSON;
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/products/product_draft_for_redactor', [], [], [], $data);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame('http://localhost/api/rest/v1/products/product_draft_for_redactor/draft', $response->headers->get('location'));

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_draft_for_redactor');
        $this->assertSame('a text', $product->getValue('a_text')->getData());

        $productDraft = $this->get('pimee_workflow.repository.product_draft')->findUserProductDraft($product, 'mary');
        $this->assertNotNull($productDraft);

        $expected = <<<JSON
{
    "values":{
        "a_text":[
            {"locale":null,"scope":null,"data":"the text"}
        ],
        "a_simple_select": [
            {"data":"optionA", "locale":null, "scope":null}
        ]
    },
    "review_statuses":{
        "a_text":[
            {"locale":null,"scope":null,"status":"draft"}
        ],
        "a_simple_select":[
            {"locale":null,"scope":null,"status":"draft"}
        ]
    }
}
JSON;
        $this->assertJsonStringEqualsJsonString($expected, json_encode($productDraft->getChanges()));
    }

    public function testSuccessfullyToUpdateDraftWithAMediaFile()
    {
        $client = $this->createAuthenticatedClient([], ['CONTENT_TYPE' => 'multipart/form-data'], null, null, 'mary', 'mary');

        $this->assertCount(3, $this->get('akeneo_file_storage.repository.file_info')->findAll());

        $image = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'akeneo.jpg';
        copy($this->getFixturePath('akeneo.jpg'), $image);

        $content = [
            'product' => '{"identifier":"product_draft_for_redactor", "attribute":"an_image", "locale":null, "scope":null}',
        ];

        $file = new UploadedFile($image, 'akeneo.jpg');
        $client->request('POST', '/api/rest/v1/media-files', $content, ['file' => $file]);
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertEmpty($response->getContent());

        // check in repo if file has been created
        $fileInfos = $this->get('akeneo_file_storage.repository.file_info')->findAll();
        $this->assertCount(4, $fileInfos);

        // check the content of file db
        $fileInfo = current($fileInfos);
        $this->assertSame('akeneo.jpg', $fileInfo->getOriginalFilename());
        $this->assertSame('image/jpeg', $fileInfo->getMimeType());
        $this->assertSame('catalogStorage', $fileInfo->getStorage());

        // check if file has been created on file system
        $mountManager = $this->get('oneup_flysystem.mount_manager');
        $fileSystem = $mountManager->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS);
        $this->assertTrue($fileSystem->has(($fileInfo->getKey())));

        // remove file from the file system
        if ($fileSystem->has($fileInfo->getKey())) {
            $fileSystem->delete($fileInfo->getKey());
        }

        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_draft_for_redactor');
        $this->assertNull($product->getValue('an_image'));

        $productDraft = $this->get('pimee_workflow.repository.product_draft')->findUserProductDraft($product, 'mary');
        $this->assertNotNull($productDraft);

        $expected = [
            'values' => [
                'a_simple_select'=> [
                    ['locale' => null,'scope' => null,'data' => 'optionA']
                ],
                'an_image' =>  [
                    ['locale' => null, 'scope' => null, 'data' => '0/c/0/1/0c01a0a71395b1ef70fd4b57d607b502fa9389bc_akeneo.jpg']
                ]
            ],
            'review_statuses' => [
                'a_simple_select'=> [
                    ['locale' => null,'scope' => null,'status' => 'draft']
                ],
                'an_image'=> [
                    ['locale' => null,'scope' => null,'status' => 'draft']
                ]
            ]
        ];

        $result = $productDraft->getChanges();
        NormalizedProductCleaner::clean($expected);
        NormalizedProductCleaner::clean($result);
        $this->assertSame($result, $expected);
    }
}
