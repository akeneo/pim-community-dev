<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\ProductDraft;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
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
class GetProductDraftEndToEnd extends ApiTestCase
{
    public function testGetProductDraftSuccessful()
    {
        $this->createDefaultProductDraft('mary', 'product_with_draft');

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $expectedResponse =
<<<JSON
{
  "uuid": "{$this->getProductUuid('product_with_draft')}",
  "identifier": "product_with_draft",
  "family": null,
  "parent": null,
  "groups": [],
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
  "quantified_associations": {},
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

        $this->updateProduct($productDraft->getEntityWithValue(), [
            'categories' => ['categoryB'],
        ]);

        $clientAsMary = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $clientAsMary->request('GET', 'api/rest/v1/products/product_with_draft/draft');

        $expectedResponseContent =
<<<JSON
{"code":404,"message":"Product \\"product_with_draft\\" does not exist or you do not have permission to access it."}
JSON;

        $response = $clientAsMary->getResponse();

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
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

        $this->updateProduct($productDraft->getEntityWithValue(), [
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

    public function testAccessDeniedWhenGetProductDraftWithoutTheAcl()
    {
        $this->createDefaultProductDraft('mary', 'product_with_draft');
        $this->removeAclFromRole('action:pim_api_product_list', 'ROLE_USER');

        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('GET', 'api/rest/v1/products/product_with_draft/draft');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog(['permission', 'proposal']);
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

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    /**
     * @param string           $userName
     * @param ProductInterface $product
     * @param array            $changes
     *
     * @return EntityWithValuesDraftInterface
     */
    protected function createEntityWithValuesDraft($userName, ProductInterface $product, array $changes)
    {
        $this->get('pim_catalog.updater.product')->update($product, $changes);

        $user = $this->get('pim_user.provider.user')->loadUserByUsername($userName);

        $productDraft = $this->get('pimee_workflow.product.builder.draft')->build(
            $product,
            $this->get('Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory')->createFromUser($user)
        );

        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);

        return $productDraft;
    }

    /**
     * @param string $userName
     * @param string $productIdentifier
     *
     * @return EntityWithValuesDraftInterface
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

        return $this->createEntityWithValuesDraft($userName, $product, [
            'values' => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'Modified US in draft', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ]
        ]);
    }

    private function getProductUuid(string $identifier): string
    {
        return $this->get('database_connection')->fetchOne(
            'SELECT BIN_TO_UUID(uuid) AS uuid FROM pim_catalog_product WHERE identifier = :identifier',
            ['identifier' => $identifier],
        );
    }
}
