<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\EndToEnd\Product\VariantProduct\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountInTransportTrait;
use AkeneoTest\Pim\Enrichment\EndToEnd\InternalApiTestCase;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class UpdateVariantProductEndToEnd extends InternalApiTestCase
{
    use AssertEventCountInTransportTrait;

    public function test_removing_category_from_variant_product_produces_an_event(): void
    {
        // apollon_blue_m & apollon_blue_l, categorized in 2 trees (master and categoryA1)
        $product = $this->createProduct(
            'apollon_optionb_false',
            'familyA',
            [
                new SetCategories(['master', 'categoryB']),
                new ChangeParent('amor'),
                new SetGroups(['groupA']),
                new SetBooleanValue('a_yes_no', null, null, false)
            ]
        );
        $normalizedProduct = $this->getProductFromInternalApi($product->getUuid());
        $this->clearMessengerTransport();
        $normalizedProduct['categories'] = ['master'];
        unset($normalizedProduct['meta']);

        $this->client->request(
            'POST',
            sprintf('/enrich/product/rest/%s', (string) $product->getUuid()),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            json_encode($normalizedProduct)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $this->assertEventCount(1, ProductUpdated::class);
    }

    protected function getProductFromInternalApi(UuidInterface $productUuid): array
    {
        $this->client->request(
            'GET',
            sprintf('/enrich/product/rest/%s', $productUuid->toString()),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        return json_decode($this->client->getResponse()->getContent(), true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticate($this->getAdminUser());

        $this->createProductModel(
            [
                'code' => 'test',
                'family_variant' => 'familyVariantA1',
                'values'  => [
                    'a_price'  => [
                        'data' => ['data' => [['amount' => '50', 'currency' => 'EUR']], 'locale' => null, 'scope' => null],
                    ],
                    'a_number_float'  => [['data' => '12.5', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area'  => [['data' => 'my pink tshirt', 'locale' => 'en_US', 'scope' => 'ecommerce']],
                ]
            ]
        );

        $this->createProductModel(
            [
                'code' => 'amor',
                'parent' => 'test',
                'family_variant' => 'familyVariantA1',
                'values'  => [
                    'a_simple_select' => [
                        ['locale' => null, 'scope' => null, 'data' => 'optionB'],
                    ],
                ],
            ]
        );
    }

    protected function getAdminUser(): UserInterface
    {
        return self::getContainer()->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
