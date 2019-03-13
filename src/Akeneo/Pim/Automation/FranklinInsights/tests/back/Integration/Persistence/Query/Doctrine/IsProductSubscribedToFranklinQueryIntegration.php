<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine;

use Akeneo\Test\Integration\TestCase;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IsProductSubscribedToFranklinQueryIntegration extends TestCase
{
    public function test_is_product_is_subscribed_to_franklin(): void
    {
        $productSubscriptionExist = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.product_subscriptions_exist')
            ->execute([15, 42]);
        $this->assertEquals([15 => false, 42 => false], $productSubscriptionExist);

        $this->insertSubscription(15);
        $this->insertSubscription(42);

        $productSubscriptionExist = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.product_subscriptions_exist')
            ->execute([15, 42]);
        $this->assertEquals([15 => true, 42 => true], $productSubscriptionExist);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function insertSubscription(int $productId): void
    {
        $sql = <<<SQL
INSERT INTO pimee_franklin_insights_subscription(subscription_id, product_id, misses_mapping, requested_asin, requested_upc, requested_brand, requested_mpn)
VALUES (:subscriptionId, :productId, 0, null, null, null, null);
SQL;
        $this->getFromTestContainer('database_connection')->executeQuery($sql, [
            'subscriptionId' => uniqid(),
            'productId' => $productId,
        ]);
    }
}
