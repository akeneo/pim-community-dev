<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use Symfony\Component\HttpFoundation\Response;

class ProductModelWorkflowIntegration extends ApiTestCase
{
    /** @var PermissionFixturesLoader */
    private $loader;

    public function testCreateProductModelDraft()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $data = [
            'values' => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'my RED tshirt', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ]
        ];

        $productModel = $this->assertProductModelDraft('sweat_edit', $data);

        $this->assertSame(
            'my pink tshirt',
            $productModel->getValue('a_localized_and_scopable_text_area', 'en_US', 'ecommerce')->getData()
        );

        $productModelDraft = $this
            ->get('pimee_workflow.repository.product_model_draft')
            ->findUserEntityWithValuesDraft($productModel, 'mary');
        $this->assertNotNull($productModelDraft);

        $expected = [
            'values' => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'my RED tshirt', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ],
            'review_statuses' => [
                'a_localized_and_scopable_text_area' => [
                    ['status' => 'draft', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ]
        ];

        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($productModelDraft->getChanges()));
    }

    public function testCreateProductModelProposal()
    {
        $this->loader->loadProductModelsFixturesForCategoryPermissions();

        $data = [
            'values'  => [
                'a_localized_and_scopable_text_area' => [
                    ['data' => 'my RED tshirt', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ]
        ];

        $productModel = $this->assertProductModelDraft('sweat_edit', $data);
        $this->assertSame(
            'my pink tshirt',
            $productModel->getValue('a_localized_and_scopable_text_area', 'en_US', 'ecommerce')->getData()
        );

        $productModelDraft = $this
            ->get('pimee_workflow.repository.product_model_draft')
            ->findUserEntityWithValuesDraft($productModel, 'mary');
        $this->assertNotNull($productModelDraft);

        $this->createProductModelProposal('sweat_edit');

        $productModelDraft = $this
            ->get('pimee_workflow.repository.product_model_draft')
            ->findUserEntityWithValuesDraft($productModelDraft->getEntityWithValue(), 'mary');
        $this->assertSame(ProductModelDraft::READY, $productModelDraft->getStatus());
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loader = new PermissionFixturesLoader($this->testKernel->getContainer());
    }

    private function assertProductModelDraft(string $code, array $data): ProductModelInterface
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request('PATCH', 'api/rest/v1/product-models/' . $code, [], [], [], json_encode($data));
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertSame(
            sprintf('http://localhost/api/rest/v1/product-models/%s/draft', $code),
            $response->headers->get('location')
        );

        return $this->get('pimee_security.repository.product_model')->findOneByIdentifier($code);
    }

    private function createProductModelProposal(string $code): void
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');
        $client->request(
            'POST',
            sprintf('api/rest/v1/product-models/%s/proposal', $code),
            [],
            [],
            [],
            '{}'
        );

        $response = $client->getResponse();
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
    }
}
