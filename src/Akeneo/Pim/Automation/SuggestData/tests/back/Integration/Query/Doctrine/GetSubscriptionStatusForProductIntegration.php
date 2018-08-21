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

namespace Akeneo\Pim\Automation\SuggestData\tests\back\Integration\Query\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscription;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetSubscriptionStatusForProductIntegration extends TestCase
{
    /**
     * Product is found directly with the EntityManager instead of the ProductRepository.
     *
     * This is because of an issue with the creation of the ProductSubscription: if the product is retrieved
     * using the repository, the Entity Manager used in the ProductSubscriptionRepository does not recognize
     * the product as a managed entity. Using directly the entity manager, it does.
     *
     * It looks like the UOW used by the ProductRepository is not the same than the one used by the EntityManager
     * injected in the ProductSubscriptionRepository…
     */
    public function test_that_a_product_is_subscribed_to_franklin(): void
    {
        $productId = $this->addProductInDatabase();
        $product = $this->get('doctrine.orm.entity_manager')->find(Product::class, $productId);

        $subscriptionRepository = $this->get('akeneo.pim.automation.suggest_data.repository.product_subscription');
        $subscriptionRepository->save(
            new ProductSubscription(
                $product,
                'fake-subscription-to-franklin',
                []
            )
        );

        $status = $this
            ->get('akeneo.pim.automation.suggest_data.query.get_subscription_status_for_product')
            ->query($productId);

        Assert::assertTrue($status);
    }

    public function test_that_a_product_is_subscribed_not_to_franklin(): void
    {
        $productId = $this->addProductInDatabase();

        $status = $this
            ->get('akeneo.pim.automation.suggest_data.query.get_subscription_status_for_product')
            ->query($productId);

        Assert::assertFalse($status);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @return int
     */
    private function addProductInDatabase(): int
    {
        $product = $this->getFromTestContainer('akeneo_integration_tests.catalog.product.builder')->build();
        $this->getFromTestContainer('pim_catalog.saver.product')->save($product);

        return $product->getId();
    }
}
