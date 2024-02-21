<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Messenger\AssertEventCountTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProduceEventOnBulkProductUpdateIntegration extends TestCase
{
    use AssertEventCountTrait;

    private UniqueValuesSet $uniqueValuesSet;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_the_bulk_product_update_event(): void
    {
        $count = 3;
        for ($i = 0; $i < $count; $i++) {
            $this->createProduct(\sprintf('t-shirt-%s', $i));
        }

        for ($i = 0; $i < $count; $i++) {
            $this->updateProduct(\sprintf('t-shirt-%s', $i), [
                new SetEnabled(false)
            ]);
        }

        $this->assertEventCount($count, ProductUpdated::class);
    }

    private function createProduct(string $identifier) : void
    {
        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithIdentifierSystemUser($identifier, [])
        );
    }


    /**
     * @param UserIntent[] $userIntents
     */
    private function updateProduct(string $identifier, array $userIntents) : void
    {
        $this->get('pim_catalog.validator.unique_value_set')->reset();
        $this->get('pim_enrich.product.message_bus')->dispatch(
            UpsertProductCommand::createWithIdentifierSystemUser($identifier, $userIntents)
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
