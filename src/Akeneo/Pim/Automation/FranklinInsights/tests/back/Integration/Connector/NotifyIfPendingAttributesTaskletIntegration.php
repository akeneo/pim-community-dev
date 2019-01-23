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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Platform\Bundle\NotificationBundle\Entity\Repository\UserNotificationRepositoryInterface;
use Akeneo\Test\Common\EntityWithValue\Builder;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Doctrine\DBAL\Types\Type;
use PHPUnit\Framework\Assert;

/**
 * Ideally, this should be an acceptance test. But here, we require a lot of
 * element from a lot of other bounded contexts, so an integration test is the
 * only way for now.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class NotifyIfPendingAttributesTaskletIntegration extends TestCase
{
    /** @var int[] */
    private $productIds;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createSubscribableProducts();
    }

    public function test_that_users_are_notified_about_missing_attribute_mappings(): void
    {
        $this->subscribeAllProducts();

        $this->notifyUsersAboutMissingMappings();

        $this->assertUserIsNotifiedforFamilies('admin', ['familyA', 'familyA1']);
        $this->assertUserIsNotifiedforFamilies('julia', ['familyA', 'familyA1']);
        $this->assertUserIsNotifiedforFamilies('mary', ['familyA']);
        $this->assertUserIsNotifiedforFamilies('kevin', ['familyA']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createSubscribableProducts(): void
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

    private function subscribeAllProducts(): void
    {
        $this->insertSubscription(
            $this->productIds['product_for_everybody'],
            true
        );
        $this->insertSubscription(
            $this->productIds['product_not_classified'],
            true
        );
        $this->insertSubscription(
            $this->productIds['product_only_for_managers'],
            true
        );
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

    private function notifyUsersAboutMissingMappings(): void
    {
        $this
            ->get('akeneo.pim.automation.franklin_insights.connector.step.notify_if_pending_attributes')
            ->getTasklet()
            ->execute();
    }

    /**
     * @param string $username
     * @param array $familyCodes
     */
    private function assertUserIsNotifiedforFamilies(string $username, array $familyCodes): void
    {
        $user = $this->getUserRepository()->findOneByIdentifier($username);

        $notifications = $this->getUserNotificationRepository()->findBy(['user' => $user]);

        Assert::assertSame(
            count($notifications),
            count($familyCodes)
        );
    }

    /**
     * @return UserRepositoryInterface
     */
    private function getUserRepository(): UserRepositoryInterface
    {
        return $this->get('pim_user.repository.user');
    }

    /**
     * @return UserNotificationRepositoryInterface
     */
    private function getUserNotificationRepository(): UserNotificationRepositoryInterface
    {
        return $this->get('pim_notification.repository.user_notification');
    }
}
