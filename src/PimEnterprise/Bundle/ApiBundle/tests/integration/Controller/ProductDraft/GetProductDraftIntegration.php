<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\ProductDraft;

use Akeneo\Test\Integration\Configuration;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\tests\integration\Normalizer\NormalizedProductCleaner;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * +----------+-----------------------------------------------+
 * |          |                   Categories                  |
 * +  Roles   +-----------------------------------------------+
 * |          |   categoryA   |   categoryA1  |   categoryB   |
 * +----------+-----------------------------------------------+
 * | Redactor |   View,Edit   |     View      |               |
 * | Manager  | View,Edit,Own | View,Edit,Own | View,Edit,Own |
 * +----------+-----------------------------------------------+
 */
class GetProductDraftIntegration extends ApiTestCase
{
    public function testGetProductDraftSuccessful()
    {
        $this->createDefaultProductDraft('mary', 'product_with_draft');

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $expectedResponse =
<<<JSON
{
  "identifier": "product_with_draft",
  "family": null,
  "parent": null,
  "groups": [],
  "variant_group": null,
  "categories": ["categoryA"],
  "enabled": true,
  "values": {
    "a_localized_and_scopable_text_area": [
      {
        "locale": "en_US",
        "scope": "ecommerce",
        "data": "Modified US in draft"
      },
      {
        "locale": "fr_FR",
        "scope": "ecommerce",
        "data": "FR ecommerce"
      }
    ]
  },
  "created": "2017-07-17T14:23:49+02:00",
  "updated": "2017-07-17T14:23:49+02:00",
  "associations": {},
  "metadata": {
        "workflow_status": "draft_in_progress"
  }
}
JSON;

        $client->request('GET', 'api/rest/v1/products/product_with_draft/draft');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $responseContent = json_decode($response->getContent(), true);
        $expectedResponseContent = json_decode($expectedResponse, true);

        NormalizedProductCleaner::clean($responseContent);
        NormalizedProductCleaner::clean($expectedResponseContent);

        $this->assertSame($expectedResponseContent, $responseContent);
    }

    public function testProductNotFound()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('GET', 'api/rest/v1/products/unknown_product/draft');

        $expectedResponseContent =
<<<JSON
{"code":404,"message":"Product \\"unknown_product\\" does not exist."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testUserCanNotViewProductAnymore()
    {
        $productDraft = $this->createDefaultProductDraft('mary', 'product_with_draft');

        $this->updateProduct($productDraft->getProduct(), [
            'categories' => ['categoryB'],
        ]);

        $clientAsMary = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $clientAsMary->request('GET', 'api/rest/v1/products/product_with_draft/draft');

        $expectedResponseContent =
<<<JSON
{"code":403,"message":"You can neither view, nor update, nor delete the product \\"product_with_draft\\", as it is only categorized in categories on which you do not have a view permission."}
JSON;

        $response = $clientAsMary->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testUserIsOwner()
    {
        $this->createDefaultProductDraft('mary', 'product_with_draft');

        $clientAsJulia = $this->createAuthenticatedClient([], [], null, null, 'julia', 'julia');
        $clientAsJulia->request('GET', 'api/rest/v1/products/product_with_draft/draft');

        $expectedResponseContent =
<<<JSON
{"code":403,"message":"You have ownership on the product \\"product_with_draft\\", you cannot create or retrieve a draft from this product."}
JSON;

        $response = $clientAsJulia->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testUserHasOnlyViewPermission()
    {
        $productDraft = $this->createDefaultProductDraft('mary', 'product_with_draft');

        $this->updateProduct($productDraft->getProduct(), [
            'categories' => ['categoryA1'],
        ]);

        $clientAsMary = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $clientAsMary->request('GET', 'api/rest/v1/products/product_with_draft/draft');

        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You only have view permission on the product \\"product_with_draft\\", you cannot create or retrieve a draft from this product."}
JSON;

        $response = $clientAsMary->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testProductHasNoDraft()
    {
        $this->createDefaultProductDraft('kevin', 'product_modified_by_kevin');

        $clientAsMary = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $clientAsMary->request('GET', 'api/rest/v1/products/product_modified_by_kevin/draft');

        $expectedResponseContent =
<<<JSON
{"code":404,"message":"There is no draft created for the product \\"product_modified_by_kevin\\"."}
JSON;

        $response = $clientAsMary->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        $rootPath = $this->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

        return new Configuration(
            [
                Configuration::getTechnicalCatalogPath(),
                $rootPath . 'tests' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'technical'
            ]
        );
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function createProduct($identifier, array $data = [])
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);

        $this->updateProduct($product, $data);

        return $product;
    }

    /**
     * @param ProductInterface $product
     * @param array            $data
     *
     * @return ProductInterface
     */
    protected function updateProduct(ProductInterface $product, array $data)
    {
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();
    }

    /**
     * @param string           $userName
     * @param ProductInterface $product
     * @param array            $changes
     *
     * @return ProductDraftInterface
     */
    protected function createProductDraft($userName, ProductInterface $product, array $changes)
    {
        $this->get('pim_catalog.updater.product')->update($product, $changes);

        $productDraft = $this->get('pimee_workflow.builder.draft')->build($product, $userName);

        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);

        return $productDraft;
    }

    /**
     * @param string $userName
     * @param string $productIdentifier
     *
     * @return ProductDraftInterface
     */
    private function createDefaultProductDraft($userName, $productIdentifier)
    {
        $product = $this->createProduct($productIdentifier, [
            'categories' => ['categoryA'],
            'values' => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'Unchanged US', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'FR ecommerce', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                ],
            ]
        ]);

        return $this->createProductDraft($userName, $product, [
            'values' => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'Modified US in draft', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ]
        ]);
    }
}
