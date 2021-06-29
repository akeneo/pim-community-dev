<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\tests\back\EndToEnd;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

class CreateTableValueEndToEnd extends ApiTestCase
{
    public function testItCreatesATableProductValue(): void
    {
        $client = $this->createAuthenticatedClient();

        $data = [
            'identifier' => 'id1',
            'values' => [
                'nutrition' => [
                    ['locale' => null, 'scope' => null, 'data' => [['ingredients' => 'bar']]],
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/products', [], [], [], json_encode($data));
        $response = $client->getResponse();
        Assert::assertSame(Response::HTTP_CREATED, $response->getStatusCode());

        $productFromDb = $this->get('pim_catalog.repository.product')->findOneByIdentifier('id1');
        Assert::assertNotNull($productFromDb);
        $value = $productFromDb->getValue('nutrition');
        Assert::assertInstanceOf(TableValue::class, $value);
        $expectedData = [['foo' => 'bar']];
        Assert::assertEqualsCanonicalizing($expectedData, $value->getData()->normalize());
    }

    public function testItInvalidatesOnInvalidFormat(): void
    {
        $client = $this->createAuthenticatedClient();

        $data = [
            'identifier' => 'id1',
            'values' => [
                'nutrition' => [
                    ['locale' => null, 'scope' => null, 'data' => 'a string'],
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/products', [], [], [], json_encode($data));
        $response = $client->getResponse();
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        Assert::assertStringContainsString(
            'Property "nutrition" expects an array as data, "string" given.',
            \json_decode($response->getContent(), true)['message']
        );
    }

    public function testItInvalidatesOnInvalidData(): void
    {
        $client = $this->createAuthenticatedClient();

        $data = [
            'identifier' => 'id1',
            'values' => [
                'nutrition' => [
                    ['locale' => null, 'scope' => null, 'data' => [['ingredients' => ['wrong_value']]]],
                ],
            ],
        ];

        $client->request('POST', 'api/rest/v1/products', [], [], [], json_encode($data));
        $response = $client->getResponse();
        Assert::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        Assert::assertStringContainsString(
            'Property "nutrition" expects an array with valid data, TODO cell should be a scalar.',
            \json_decode($response->getContent(), true)['message']
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $attribute = new Attribute();
        $attribute->setEntityType(Product::class);
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => 'nutrition',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'table_configuration' => [
                ['code' => 'ingredients', 'data_type' => 'select', 'labels' => ['en_US' => 'Ingredients'],
                    'options' => [
                        ['code' => 'bar']
                    ],
                ],
                ['code' => 'quantity', 'data_type' => 'text', 'labels' => ['en_US' => 'Quantity']],
            ]
        ]);
        $violations = $this->get('validator')->validate($attribute);
        self::assertCount(0, $violations, sprintf('Attribute is not valid: %s', $violations));
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }
}
