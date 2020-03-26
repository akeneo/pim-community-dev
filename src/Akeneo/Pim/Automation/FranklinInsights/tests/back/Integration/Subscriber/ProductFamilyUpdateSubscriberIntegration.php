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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Subscriber;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveIdentifiersMappingCommand;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\ProductSubscription;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;

class ProductFamilyUpdateSubscriberIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->activateFranklinConnection();
        $this->createIdentifiersMapping();

        $this->get('akeneo.pim.automation.franklin_insights.application.configuration.query.get_connection_status_handler')
            ->clearCache();
    }

    public function test_it_removes_the_subscription_only_if_the_family_is_removed_from_the_subscribed_product()
    {
        $productA = $this->createProduct('product_A');
        $productB = $this->createProduct('product_B');
        $productC = $this->createProduct('product_C');

        $this->subscribeProduct($productA->getId());
        $this->subscribeProduct($productB->getId());
        $this->subscribeProduct($productC->getId());

        $this->updateProduct($productA, ['family' => null]);
        $this->updateProduct($productB, ['family' => 'familyA1']);
        $this->updateProduct($productC,
            ['values' => ['a_text' => [['scope' => null, 'locale' => null, 'data' => 'some text']]]]);

        $this->assertProductSubscriptionDoesNotExist($productA->getId());
        $this->assertProductSubscriptionExist($productB->getId());
        $this->assertProductSubscriptionExist($productC->getId());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function createProduct(string $identifier): ProductInterface
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier($identifier)
            ->withFamily('familyA')
            ->withCategories('master')
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    private function subscribeProduct(int $productId): void
    {
        $subscriptionId = sprintf('subscription_id_%d', $productId);
        $productSubscription = new ProductSubscription(
            new ProductId($productId),
            new SubscriptionId($subscriptionId),
            ['asin' => 'an_asin_code']
        );

        $this->get('akeneo.pim.automation.franklin_insights.repository.product_subscription')->save($productSubscription);
    }

    private function updateProduct(ProductInterface $product, array $productData): void
    {
        $this->get('pim_catalog.updater.product')->update($product, $productData);
        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function assertProductSubscriptionDoesNotExist(int $productId): void
    {
        $subscription = $this->get('akeneo.pim.automation.franklin_insights.repository.product_subscription')
            ->findOneByProductId(new ProductId($productId));

        $this->assertNull($subscription);
    }

    private function assertProductSubscriptionExist(int $productId): void
    {
        $subscription = $this->get('akeneo.pim.automation.franklin_insights.repository.product_subscription')
            ->findOneByProductId(new ProductId($productId));

        $this->assertNotNull($subscription);
    }

    private function activateFranklinConnection(): void
    {
        $configuration = new Configuration();
        $configuration->setToken(new Token('6fb53a23-5d5c-454e-b69b-85ee0adfccc4'));

        $this->get('akeneo.pim.automation.franklin_insights.repository.configuration')->save($configuration);
    }

    private function createIdentifiersMapping(): void
    {
        $asin = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => 'asin',
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ]);

        $this->get('pim_catalog.saver.attribute')->save($asin);

        $mapping = array_fill_keys(IdentifiersMapping::FRANKLIN_IDENTIFIERS, null);
        $mapping['asin'] = 'asin';

        $command = new SaveIdentifiersMappingCommand($mapping);
        $this->get('akeneo.pim.automation.franklin_insights.handler.update_identifiers_mapping')->handle($command);
    }
}
