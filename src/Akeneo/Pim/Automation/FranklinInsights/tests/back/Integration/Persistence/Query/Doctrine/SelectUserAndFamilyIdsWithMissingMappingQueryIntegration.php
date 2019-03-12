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

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectUserAndFamilyIdsWithMissingMappingQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Common\EntityWithValue\Builder;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class SelectUserAndFamilyIdsWithMissingMappingQueryIntegration extends TestCase
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

    public function test_that_it_selects_users_owning_products_with_missing_mapping(): void
    {
        $this->insertSubscription(
            $this->productIds['product_for_everybody'],
            true
        );
        $this->insertSubscription(
            $this->productIds['product_only_for_managers'],
            true
        );

        $queryResult = $this->getUserAndFamilyIdsQuery()->execute();

        Assert::assertEqualsCanonicalizing(
            [
                $this->getUserId('admin') => [$this->getFamilyId('familyA'), $this->getFamilyId('familyA1')],
                $this->getUserId('julia') => [$this->getFamilyId('familyA'), $this->getFamilyId('familyA1')],
                $this->getUserId('mary') => [$this->getFamilyId('familyA')],
                $this->getUserId('kevin') => [$this->getFamilyId('familyA')],
            ],
            $queryResult
        );
    }

    public function test_that_it_selects_users_for_non_classified_products_with_missing_mapping(): void
    {
        $this->insertSubscription(
            $this->productIds['product_not_classified'],
            true
        );
        $this->insertSubscription(
            $this->productIds['product_only_for_managers'],
            true
        );

        $queryResult = $this->getUserAndFamilyIdsQuery()->execute();

        Assert::assertEqualsCanonicalizing(
            [
                $this->getUserId('admin') => [$this->getFamilyId('familyA'), $this->getFamilyId('familyA1')],
                $this->getUserId('julia') => [$this->getFamilyId('familyA'), $this->getFamilyId('familyA1')],
                $this->getUserId('mary') => [$this->getFamilyId('familyA')],
                $this->getUserId('kevin') => [$this->getFamilyId('familyA')],
            ],
            $queryResult
        );
    }

    public function test_that_it_selects_no_user_if_there_are_no_missing_mapping(): void
    {
        $this->insertSubscription(
            $this->productIds['product_for_everybody'],
            false
        );

        $queryResult = $this->getUserAndFamilyIdsQuery()->execute();

        Assert::assertSame([], $queryResult);
    }

    public function test_that_it_selects_no_user_if_there_are_no_subscriptions(): void
    {
        $queryResult = $this->getUserAndFamilyIdsQuery()->execute();

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
        $productForEverybody = $this->productBuilder()
            ->withIdentifier('product_for_everybody')
            ->withFamily('familyA')
            ->withCategories('master')
            ->build();
        $this->validate($productForEverybody);

        $productNotClassified = $this->productBuilder()
            ->withIdentifier('product_not_classified')
            ->withFamily('familyA')
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
                $productForEverybody,
                $productNotClassified,
                $productOnlyForManagers,
            ]
        );

        $this->productIds = [
            'product_for_everybody' => $productForEverybody->getId(),
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
     * @return SelectUserAndFamilyIdsWithMissingMappingQuery
     */
    private function getUserAndFamilyIdsQuery(): SelectUserAndFamilyIdsWithMissingMappingQuery
    {
        return $this->get('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_user_and_family_ids_with_missing_mapping');
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

    /**
     * @param string $familyCode
     *
     * @return int
     */
    private function getFamilyId(string $familyCode): int
    {
        return $this->get('pim_catalog.repository.family')->findOneByIdentifier($familyCode)->getId();
    }
}
