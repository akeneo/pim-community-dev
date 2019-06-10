<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectUserIdsAndFamilyCodesWithMissingMappingQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Common\EntityWithValue\Builder;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SelectUserIdsAndFamilyCodesWithMissingMappingQueryIntegration extends TestCase
{
    /** @var int[] */
    private $productIds;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProducts();
    }

    /**
     *  product_for_everybody_1   : familyA
     *  product_for_everybody_2   : familyA
     *  product_only_for_managers : familyA1
     *  product_not_classified    : familyA2
     */
    public function test_that_it_selects_users_owning_products_with_missing_mapping(): void
    {
        $this->insertSubscription($this->productIds['product_for_everybody_1'], true);
        $this->insertSubscription($this->productIds['product_for_everybody_2'], true);
        $this->insertSubscription($this->productIds['product_not_classified'], true);
        $this->insertSubscription($this->productIds['product_only_for_managers'], true);

        $queryResult = $this->getUserIdsAndFamilyCodesQuery()->execute();

        Assert::assertEqualsCanonicalizing(
            [
                $this->getUserId('admin') => ['familyA', 'familyA1', 'familyA2'],
                $this->getUserId('julia') => ['familyA', 'familyA1', 'familyA2'],
                $this->getUserId('mary') => ['familyA', 'familyA2'],
                $this->getUserId('kevin') => ['familyA', 'familyA2'],
            ],
            $queryResult
        );
    }

    public function test_that_it_does_not_select_users_that_do_not_own_any_subscribed_products_with_missing_mapping()
    {
        $this->insertSubscription($this->productIds['product_only_for_managers'], true);

        $queryResult = $this->getUserIdsAndFamilyCodesQuery()->execute();

        Assert::assertEqualsCanonicalizing(
            [
                $this->getUserId('admin') => ['familyA1'],
                $this->getUserId('julia') => ['familyA1'],
            ],
            $queryResult
        );
    }

    public function test_that_it_selects_no_user_if_there_are_no_missing_mapping(): void
    {
        $this->insertSubscription($this->productIds['product_for_everybody_1'], false);

        $queryResult = $this->getUserIdsAndFamilyCodesQuery()->execute();

        Assert::assertSame([], $queryResult);
    }

    public function test_that_it_selects_no_user_if_there_are_no_subscriptions(): void
    {
        $queryResult = $this->getUserIdsAndFamilyCodesQuery()->execute();

        Assert::assertSame([], $queryResult);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProducts(): void
    {
        $productForEverybody1 = $this->productBuilder()
            ->withIdentifier('product_for_everybody_1')
            ->withFamily('familyA')
            ->withCategories('master')
            ->build();
        $this->validate($productForEverybody1);

        $productForEverybody2 = $this->productBuilder()
            ->withIdentifier('product_for_everybody_2')
            ->withFamily('familyA')
            ->withCategories('master')
            ->build();
        $this->validate($productForEverybody2);

        $productNotClassified = $this->productBuilder()
            ->withIdentifier('product_not_classified')
            ->withFamily('familyA2')
            ->build();
        $this->validate($productNotClassified);

        $productOnlyForManagers = $this->productBuilder()
            ->withIdentifier('product_only_for_managers')
            ->withFamily('familyA1')
            ->withCategories('categoryA')
            ->build();
        $this->validate($productOnlyForManagers);

        $this->getFromTestContainer('pim_catalog.saver.product')->saveAll(
            [
                $productForEverybody1,
                $productForEverybody2,
                $productNotClassified,
                $productOnlyForManagers,
            ]
        );

        $this->productIds = [
            'product_for_everybody_1' => $productForEverybody1->getId(),
            'product_for_everybody_2' => $productForEverybody2->getId(),
            'product_not_classified' => $productNotClassified->getId(),
            'product_only_for_managers' => $productOnlyForManagers->getId(),
        ];
    }

    /**
     * @param ProductInterface $product
     *
     * @throws \Exception
     */
    private function validate(ProductInterface $product): void
    {
        $violations = $this->getFromTestContainer('pim_catalog.validator.product')->validate($product);

        if (0 < count($violations)) {
            throw new \Exception((string) $violations);
        }
    }

    /**
     * @param int $productId
     * @param bool $isMappingMissing
     */
    private function insertSubscription(int $productId, bool $isMappingMissing): void
    {
        $query = <<<SQL
INSERT INTO pimee_franklin_insights_subscription (product_id, subscription_id, misses_mapping) 
VALUES (:productId, :subscriptionId, :isMappingMissing)
SQL;

        $queryParameters = [
            'productId' => $productId,
            'subscriptionId' => uniqid(),
            'isMappingMissing' => $isMappingMissing,
        ];
        $types = [
            'productId' => Type::INTEGER,
            'subscriptionId' => Type::STRING,
            'isMappingMissing' => Type::BOOLEAN,
        ];

        $this->get('doctrine.orm.entity_manager')->getConnection()->executeUpdate($query, $queryParameters, $types);
    }

    /**
     * @return Builder\Product
     */
    private function productBuilder(): Builder\Product
    {
        return $this->getFromTestContainer('akeneo_integration_tests.catalog.product.builder');
    }

    /**
     * @return SelectUserIdsAndFamilyCodesWithMissingMappingQuery
     */
    private function getUserIdsAndFamilyCodesQuery(): SelectUserIdsAndFamilyCodesWithMissingMappingQuery
    {
        return $this->get('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_user_ids_and_family_codes_with_missing_mapping');
    }

    /**
     * @param string $username
     *
     * @return int
     */
    private function getUserId(string $username): int
    {
        return $this->get('pim_user.provider.user')->loadUserByUsername($username)->getId();
    }
}
