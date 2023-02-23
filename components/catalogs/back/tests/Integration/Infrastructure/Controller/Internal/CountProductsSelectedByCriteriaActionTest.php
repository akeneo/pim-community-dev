<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Controller\Internal;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use PHPUnit\Framework\Assert;

/**
 * @covers \Akeneo\Catalogs\Infrastructure\Controller\Internal\CountProductsSelectedByCriteriaAction
 */
class CountProductsSelectedByCriteriaActionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsNumberOfProductsSelectedByCriteria(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');

        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);
        $this->createProduct('tshirt-red', [new SetEnabled(true)]);
        $this->createProduct('tshirt-yellow', [new SetEnabled(false)]);

        $client->request(
            'POST',
            '/rest/catalogs/product-selection-criteria/product/count',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            \json_encode([
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ]),
        );

        $response = $client->getResponse();
        Assert::assertEquals(200, $response->getStatusCode());

        $count = \json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertEquals(2, $count);
    }

    public function testReturnsABadRequestIfTheSelectionCriteriaAreInvalid(): void
    {
        $client = $this->getAuthenticatedInternalApiClient('admin');

        $client->request(
            'POST',
            '/rest/catalogs/product-selection-criteria/product/count',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            \json_encode([
                [
                    'field' => '',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ]),
        );

        $response = $client->getResponse();
        Assert::assertEquals(400, $response->getStatusCode());
    }
}
