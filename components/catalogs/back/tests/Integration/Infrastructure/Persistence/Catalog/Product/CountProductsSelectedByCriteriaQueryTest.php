<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Exception\InvalidProductSelectionCriteriaException;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\CountProductsSelectedByCriteriaQueryInterface;
use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use PHPUnit\Framework\Assert;

class CountProductsSelectedByCriteriaQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItCountsTheNumberOfProductsInTheSelection(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);
        $this->createProduct('tshirt-red', [new SetEnabled(true)]);
        $this->createProduct('tshirt-yellow', [new SetEnabled(false)]);

        $productSelectionCriteria = [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ];

        $count = self::getContainer()->get(CountProductsSelectedByCriteriaQueryInterface::class)->execute($productSelectionCriteria);
        Assert::assertEquals(2, $count);
    }

    public function testThereIsNoProductInTheSelection(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);
        $this->createProduct('tshirt-green', [new SetEnabled(true)]);

        $productSelectionCriteria = [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => false,
            ],
        ];

        $count = self::getContainer()->get(CountProductsSelectedByCriteriaQueryInterface::class)->execute($productSelectionCriteria);
        Assert::assertEquals(0, $count);
    }

    public function testTheProductSelectionIsNotValid(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $productSelectionCriteria = [
            [
                'field' => '',
                'operator' => Operator::EQUALS,
                'value' => false,
            ],
        ];

        $this->expectException(InvalidProductSelectionCriteriaException::class);
        self::getContainer()->get(CountProductsSelectedByCriteriaQueryInterface::class)->execute($productSelectionCriteria);
    }
}
