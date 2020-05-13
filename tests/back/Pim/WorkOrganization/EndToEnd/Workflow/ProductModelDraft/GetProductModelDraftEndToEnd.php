<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\ProductModelDraft;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use Symfony\Component\HttpFoundation\Response;

class GetProductModelDraftEndToEnd extends ApiTestCase
{
    public function testGetRootProductModelDraft()
    {
        $this->createProductModelDraft('mary', 'jack');

        $client = $this->createAuthenticatedClient([], [], null, null, 'Mary', 'Mary');
        $expectedResponse =
<<<JSON
{
  "code": "jack",
  "family_variant": "clothing_color_size",
  "parent": null,
  "categories": ["tshirts"],
  "values": {
    "collection": [
      {
        "locale": null,
        "scope": null,
        "data": ["summer_2017"]
      }
    ],
    "erp_name": [
      {
        "locale": "en_US",
        "scope": null,
        "data": "Jack" 
      }
    ],
    "name": [
      {
        "locale": "en_US",
        "scope": null,
        "data": "jack"
      }
    ],
    "wash_temperature": [
      {
        "locale": null,
        "scope": null,
        "data": "hand"
      }
    ]
  },
  "created": "2017-07-17T14:23:49+02:00",
  "updated": "2017-07-17T14:23:49+02:00",
  "associations": {},
  "quantified_associations": [],
  "family": "clothing",
  "metadata": {
      "workflow_status": "draft_in_progress"
  }
}
JSON;

        $client->request('GET', 'api/rest/v1/product-models/jack/draft');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $responseContent = json_decode($response->getContent(), true);
        $expectedResponseContent = json_decode($expectedResponse, true);

        NormalizedProductCleaner::clean($responseContent);
        NormalizedProductCleaner::clean($expectedResponseContent);

        $this->assertSame($expectedResponseContent, $responseContent);
    }

    public function testProductModelNotFound()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'Mary', 'Mary');
        $client->request('GET', 'api/rest/v1/product-models/unknown/draft');

        $expectedResponseContent =
<<<JSON
{"code":404,"message":"Product model \\"unknown\\" does not exist."}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testUserIsOwner()
    {
        $this->createProductModelDraft('mary', 'jack');

        $clientAsJulia = $this->createAuthenticatedClient([], [], null, null, 'Julia', 'Julia');
        $clientAsJulia->request('GET', 'api/rest/v1/product-models/jack/draft');

        $expectedResponseContent =
<<<JSON
{"code":403,"message":"You have ownership on the product model \\"jack\\", you cannot create or retrieve a draft from this product model."}
JSON;

        $response = $clientAsJulia->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testUserHasOnlyViewPermission()
    {
        $productDraft = $this->createProductModelDraft('mary', 'jack');

        $this->get('pim_catalog.updater.product_model')->update($productDraft->getEntityWithValue(), [
            'categories' => ['print_shoes'],
        ]);
        $this->get('pim_catalog.saver.product_model')->save($productDraft->getEntityWithValue());

        $clientAsMary = $this->createAuthenticatedClient([], [], null, null, 'Mary', 'Mary');
        $clientAsMary->request('GET', 'api/rest/v1/product-models/jack/draft');

        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You only have view permission on the product model \\"jack\\", you cannot create or retrieve a draft from this product model."}
JSON;

        $response = $clientAsMary->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testProductModelHasNoDraft()
    {
        $clientAsMary = $this->createAuthenticatedClient([], [], null, null, 'Mary', 'Mary');
        $clientAsMary->request('GET', 'api/rest/v1/product-models/jack/draft');

        $expectedResponseContent =
<<<JSON
{"code":404,"message":"There is no draft created for the product model \\"jack\\"."}
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
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    private function createProductModelDraft(string $userName, string $identifier): EntityWithValuesDraftInterface
    {
        $productModel = $this->get('pim_catalog.repository.product_model_without_permission')->findOneByIdentifier($identifier);

        $this->get('pim_catalog.updater.product_model')->update($productModel, [
            'values' => [
                'wash_temperature' => [
                    ['data' => 'hand', 'locale' => null, 'scope' => null],
                ],
            ]
        ]);

        $user = $this->get('pim_user.provider.user')->loadUserByUsername($userName);

        $productModelDraft = $this->get('pimee_workflow.product_model.builder.draft')->build(
            $productModel,
            $this->get('Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory')->createFromUser($user)
        );

        $this->get('pimee_workflow.saver.product_model_draft')->save($productModelDraft);

        return $productModelDraft;
    }
}
