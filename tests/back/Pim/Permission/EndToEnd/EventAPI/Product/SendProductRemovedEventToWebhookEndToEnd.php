<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\EventAPI\Product;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Webhook\MessageHandler\BusinessEventHandler;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Tests\EndToEnd\GuzzleJsonHistoryContainer;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Ramsey\Uuid\Uuid;

class SendProductRemovedEventToWebhookEndToEnd extends ApiTestCase
{
    private ConnectionLoader $connectionLoader;
    private IdentifiableObjectRepositoryInterface $userGroupRepository;
    private GuzzleJsonHistoryContainer $historyContainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->userGroupRepository = $this->get('pim_user.repository.group');
        $this->historyContainer = $this->get('Akeneo\Connectivity\Connection\Tests\EndToEnd\GuzzleJsonHistoryContainer');

        $this->get('akeneo_integration_tests.loader.permissions')->loadProductsAndProductModelsForRemovedEvents();
        $redactorGroupConnection = $this->getRedactorGroupConnection();
        $this->get('akeneo_connectivity.connection.fixtures.webhook_loader')->initWebhook(
            $redactorGroupConnection->code()
        );
    }

    public function test_that_a_connection_with_access_to_only_one_category_of_the_product_is_still_notified_about_its_removal(
    ): void
    {
        $message = new BulkEvent(
            [
                new ProductRemoved(
                    Author::fromNameAndType('ecommerce', 'ui'), [
                        'identifier' => 'product_with_one_category_viewable_by_redactor_and_one_category_not_viewable_by_redactor',
                        'uuid' => Uuid::uuid4(),
                        'category_codes' => ['view_category', 'category_without_right'],
                    ]
                ),
            ]
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        $this->assertCount(1, $this->historyContainer);
    }

    public function test_that_a_connection_that_does_not_see_a_product_is_not_notified_about_its_removal(): void
    {
        $message = new BulkEvent(
            [
                new ProductRemoved(
                    Author::fromNameAndType('ecommerce', 'ui'), [
                        'identifier' => 'product_not_viewable_by_redactor',
                        'uuid' => Uuid::uuid4(),
                        'category_codes' => ['category_without_right'],
                    ]
                ),
            ]
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        $this->assertCount(0, $this->historyContainer);
    }

    private function getRedactorGroupConnection(): ConnectionWithCredentials
    {
        $connection = $this->connectionLoader->createConnection(
            'erp',
            'erp',
            FlowType::DATA_SOURCE,
            false
        );
        $redactorGroup = $this->userGroupRepository->findOneByIdentifier('redactor');

        $this->connectionLoader->update(
            $connection->code(),
            $connection->label(),
            $connection->flowType(),
            $connection->image(),
            $connection->userRoleId(),
            (string)$redactorGroup->getId(),
            $connection->auditable(),
        );

        return $connection;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
