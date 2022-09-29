<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\EndToEnd\Workflow\ProductDraft;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

final class GetProductDraftByUuidEndToEnd extends ApiTestCase
{
    private string $productWithDraftUuid = '456b8659-f454-46a8-ace1-9f57935b3d3e';
    private string $product1Uuid = 'c6da7ec3-d93a-435f-a125-aedfcfe05461';
    private string $product2Uuid = '591a7740-6e34-4c09-b00a-8fb858e4ceea';
    private string $nonViewableProductUuid = '3d3d2040-fe02-4c07-ab13-83069962dbc3';

    public function testGetProductDraftSuccessful(): void
    {
        $expectedResponse = <<<JSON
            {
              "uuid": "{$this->productWithDraftUuid}",
              "family": "familyA",
              "parent": null,
              "groups": [],
              "categories": ["categoryA"],
              "enabled": true,
              "values": {
                "sku": [{
                    "locale": null,
                    "scope": null,
                    "data": "product_with_draft"
                }],
                "a_localized_and_scopable_text_area": [
                  {
                    "locale": "en_US",
                    "scope": "ecommerce",
                    "data": "The updated US text"
                  },
                  {
                    "locale": "fr_FR",
                    "scope": "tablet",
                    "data": "Texte original FR"
                  }
                ]
              },
              "created": "2017-07-17T14:23:49+02:00",
              "updated": "2017-07-17T14:23:49+02:00",
              "associations": {
                "PACK": {
                  "groups": [],
                  "product_models": [],
                  "products": []
                },    
                "SUBSTITUTION": {
                  "groups": [],
                  "product_models": [],
                  "products": []
                },
                "UPSELL": {
                  "groups": [],
                  "product_models": [],
                  "products": ["{$this->product1Uuid}"]
                },
                "X_SELL": {
                  "groups": [],
                  "product_models": [],
                  "products": []
                }
              },
              "quantified_associations": {
                "PRODUCTSET": {
                  "product_models": [],
                  "products": [
                    {"uuid": "{$this->product2Uuid}", "quantity": 5}
                  ]
                }
              },
              "metadata": {
                    "workflow_status": "draft_in_progress"
              }
            }
        JSON;
        $response = $this->getResponse($this->productWithDraftUuid, 'mary');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $responseContent = json_decode($response->getContent(), true);
        $expectedResponseContent = json_decode($expectedResponse, true);

        NormalizedProductCleaner::clean($responseContent);
        NormalizedProductCleaner::clean($expectedResponseContent);

        $this->assertEquals($expectedResponseContent, $responseContent);
    }

    public function testUnknownProduct(): void
    {
        $uuid = Uuid::uuid4()->toString();
        $expectedResponseContent =
<<<JSON
{"code":404,"message":"Product \\"{$uuid}\\" does not exist or you do not have permission to access it."}
JSON;

        $response = $this->getResponse($uuid, 'mary');
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testNonViewableProduct(): void
    {
        $this->upsertProduct($this->productWithDraftUuid, 'julia', [new SetCategories(['categoryB'])]);

        $expectedResponseContent = <<<JSON
{"code":404,"message":"Product \\"{$this->productWithDraftUuid}\\" does not exist or you do not have permission to access it."}
JSON;

        $response = $this->getResponse($this->productWithDraftUuid, 'mary');

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testUserIsOwner(): void
    {
        $expectedResponseContent = <<<JSON
{"code":403,"message":"You have ownership on the product \\"{$this->productWithDraftUuid}\\", you cannot create or retrieve a draft from this product."}
JSON;

        $response = $this->getResponse($this->productWithDraftUuid, 'julia');
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($expectedResponseContent, $response->getContent());
    }

    public function testUserHasOnlyViewPermission(): void
    {
        $this->upsertProduct($this->productWithDraftUuid, 'julia', [
            new SetCategories(['categoryA1']),
        ]);

        $expectedResponseContent =
            <<<JSON
{"code":403,"message":"You only have view permission on the product \\"{$this->productWithDraftUuid}\\", you cannot create or retrieve a draft from this product."}
JSON;

        $response = $this->getResponse($this->productWithDraftUuid, 'mary');
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testProductHasNoDraft(): void
    {
        $this->get('database_connection')->executeStatement(
            'DELETE FROM pimee_workflow_product_draft WHERE product_uuid = :uuid;',
            ['uuid' => Uuid::fromString($this->productWithDraftUuid)->getBytes()]
        );

        $expectedResponseContent =
<<<JSON
{"code":404,"message":"There is no draft created for the product \\"{$this->productWithDraftUuid}\\"."}
JSON;

        $response = $this->getResponse($this->productWithDraftUuid, 'mary');
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame($response->getContent(), $expectedResponseContent);
    }

    public function testAccessDeniedWhenGetProductDraftWithoutTheAcl(): void
    {
        $this->removeAclFromRole('action:pim_api_product_list', 'ROLE_USER');

        $response = $this->getResponse($this->productWithDraftUuid, 'mary');
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // create quantified association type
        $quantifiedAssociationType = $this->get('pim_catalog.factory.association_type')->create();
        $this->get('pim_catalog.updater.association_type')->update(
            $quantifiedAssociationType,
            [
                'code' => 'PRODUCTSET',
                'is_quantified' => true,
            ]
        );
        $this->get('pim_catalog.saver.association_type')->save($quantifiedAssociationType);

        // create 3 products
        $this->upsertProduct($this->product1Uuid, 'julia', [new SetIdentifierValue('sku', 'product_1')]);
        $product2 = $this->upsertProduct($this->product2Uuid, 'julia', [new SetIdentifierValue('sku', 'product_2')]);
        $this->upsertProduct($this->nonViewableProductUuid, 'julia', [
            new SetIdentifierValue('sku', 'non_viewable_product'),
            new SetCategories(['categoryB']),
        ]);
        $productWithDraft = $this->upsertProduct($this->productWithDraftUuid, 'julia', [
            new SetIdentifierValue('sku', 'product_with_draft'),
            new AddCategories(['categoryA']),
            new SetFamily('familyA'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce', 'en_US', 'Original text US'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'fr_FR', 'Texte original FR'),
            new AssociateProducts('UPSELL', ['product_1', 'non_viewable_product']),
            new AssociateQuantifiedProducts('PRODUCTSET', [new QuantifiedEntity((string) $product2->getUuid(), 5)])
        ]);

        // create a draft for mary
        $valueEn = $productWithDraft->getValue('a_localized_and_scopable_text_area', 'en_US', 'ecommerce');
        $productWithDraft->removeValue($valueEn)->addValue(
            ScalarValue::scopableLocalizableValue(
                'a_localized_and_scopable_text_area',
                'The updated US text',
                'ecommerce',
                'en_US',
            )
        );
        $productDraft = $this->get('pimee_workflow.product.builder.draft')->build(
            $productWithDraft,
            $this->get('Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory')->createFromUser($this->user('mary'))
        );
        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog(['permission', 'proposal']);
    }

    private function upsertProduct(string $uuid, string $username, array $userIntents): ProductInterface
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($username);
        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithUuid(
                $this->user($username)->getId(),
                ProductUuid::fromUuid(Uuid::fromString($uuid)),
                $userIntents
            )
        );
        $this->get('pim_catalog.validator.unique_value_set')->reset();

        return $this->get('pim_catalog.repository.product')->find($uuid);
    }

    private function user(string $username): UserInterface
    {
        return $this->get('pim_user.repository.user')->findOneByIdentifier($username);
    }

    private function getResponse(string $uuid, string $username): Response
    {
        $client = $this->createAuthenticatedClient([], [], null, null, $username, $username);
        $client->request('GET', "api/rest/v1/products-uuid/{$uuid}/draft");

        return $client->getResponse();
    }
}
