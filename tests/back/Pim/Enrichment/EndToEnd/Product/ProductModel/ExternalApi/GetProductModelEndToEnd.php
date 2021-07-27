<?php

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\ProductModel\ExternalApi;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Model\AclPermission;
use Oro\Bundle\SecurityBundle\Model\AclPrivilege;
use Oro\Bundle\SecurityBundle\Model\AclPrivilegeIdentity;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class GetProductModelEndToEnd extends ApiTestCase
{
    protected function removeAclFromRole(string $aclPrivilegeIdentityId): void
    {
        $aclManager = $this->get('oro_security.acl.manager');
        $role = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_ADMINISTRATOR');
        $privilege = new AclPrivilege();
        $identity = new AclPrivilegeIdentity($aclPrivilegeIdentityId);
        $privilege
            ->setIdentity($identity)
            ->addPermission(new AclPermission('EXECUTE', AccessLevel::NONE_LEVEL));
        $aclManager->getPrivilegeRepository()->savePrivileges(
            $aclManager->getSid($role),
            new ArrayCollection([$privilege])
        );
        $aclManager->flush();
        $aclManager->clearCache();
    }

    /**
     * @group ce
     * @group critical
     */
    public function testSuccessfullyGetProductModel()
    {
        $this->addAssociationsToProductModel('model-biker-jacket-leather');

        $standardProductModel = [
            'code' => 'model-biker-jacket-leather',
            'family' => 'clothing',
            'family_variant' => 'clothing_material_size',
            'parent' => 'model-biker-jacket',
            'categories' => ['master_men_blazers'],
            'values' => [
                'color' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'antique_white',
                    ]
                ],
                'material' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'leather',
                    ]
                ],
                'variation_name' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'Biker jacket leather',
                    ]
                ],
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope' => null,
                        'data' => 'Biker jacket',
                    ]
                ],
                'collection' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => ['summer_2017']
                    ]
                ],
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope' => 'ecommerce',
                        'data' => 'Biker jacket',
                    ]
                ]
            ],
            'created' => '2017-10-02T15:03:55+02:00',
            'updated' => '2017-10-02T15:03:55+02:00',
            'associations'  => [
                'X_SELL' => ['groups' => [], 'products' => ['biker-jacket-leather-m'], 'product_models' => ['model-biker-jacket-polyester']],
                'PACK' => ['groups' => [], 'products' => [], 'product_models' => []],
                'COMPATIBILITY' => ['groups' => [], 'products' => [], 'product_models' => []],
                'SUBSTITUTION' => ['groups' => [], 'products' => [], 'product_models' => []],
                'UPSELL' => ['groups' => [], 'products' => [], 'product_models' => []]
            ],
            'quantified_associations' => [],
        ];
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/product-models/model-biker-jacket-leather');

        $response = $client->getResponse();
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertResponse($response, $standardProductModel);
    }

    public function testAccessDeniedOnGetAProductModelIfNoPermission()
    {
        $this->addAssociationsToProductModel('model-biker-jacket-leather');

        $client = $this->createAuthenticatedClient();
        $this->removeAclFromRole('action:pim_api_product_list');
        $client->request('GET', 'api/rest/v1/product-models/model-biker-jacket-leather');

        $expectedResponse = <<<JSON
{
    "code": 403,
    "message": "Access forbidden. You are not allowed to list product models."
}
JSON;

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedResponse, $response->getContent());
    }

    public function testFailToGetANonExistingProductModel()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', 'api/rest/v1/product-models/model-bayqueur-jaquette');

        $response = $client->getResponse();
        Assert::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    protected function addAssociationsToProductModel($productModelCode)
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByCode($productModelCode);

        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'associations' => [
                    'X_SELL' => [
                        'products' => ['biker-jacket-leather-m'],
                        'product_models' => ['model-biker-jacket-polyester'],                    ]
                ],
            ]
        );

        $errors = $this->get('pim_catalog.validator.product_model')->validate($productModel);

        Assert::assertCount(0, $errors);

        $this->get('pim_catalog.saver.product_model')->save($productModel);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * @param Response $response
     * @param array    $expected
     */
    private function assertResponse(Response $response, array $expected)
    {
        $result = json_decode($response->getContent(), true);

        NormalizedProductCleaner::clean($expected);
        NormalizedProductCleaner::clean($result);

        Assert::assertSame($expected, $result);
    }
}
