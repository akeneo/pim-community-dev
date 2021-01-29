<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\EventAPI\Product;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\MessageHandler\BusinessEventHandler;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelRemoved;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SendProductModelRemovedEventToWebhookEndToEnd extends ApiTestCase
{
    private ConnectionLoader $connectionLoader;
    private IdentifiableObjectRepositoryInterface $userGroupRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->userGroupRepository = $this->get('pim_user.repository.group');

        $this->get('akeneo_integration_tests.loader.permissions')->loadProductsAndProductModelsForRemovedEvents();
        $redactorGroupConnection = $this->getRedactorGroupConnection();
        $this->get('akeneo_connectivity.connection.fixtures.webhook_loader')->initWebhook(
            $redactorGroupConnection->code()
        );
    }

    public function test_that_a_connection_with_access_to_only_one_category_of_the_product_model_is_still_notified_about_its_removal(
    ): void
    {
        /** @var HandlerStack $handlerStack */
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(new MockHandler([new Response(200)]));

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new BulkEvent(
            [
                new ProductModelRemoved(
                    Author::fromNameAndType('ecommerce', 'ui'), [
                        'code' => 'product_model_with_one_category_viewable_by_redactor_and_one_category_not_viewable_by_redactor',
                        'category_codes' => ['view_category', 'category_without_right'],
                    ]
                ),
            ]
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        $this->assertCount(1, $container);
    }

    public function test_that_a_connection_that_does_not_see_a_product_model_is_not_notified_about_its_removal(): void
    {
        /** @var HandlerStack $handlerStack */
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(new MockHandler([new Response(200)]));

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new BulkEvent(
            [
                new ProductModelRemoved(
                    Author::fromNameAndType('ecommerce', 'ui'), [
                        'code' => 'product_model_not_viewable',
                        'category_codes' => ['category_without_right'],
                    ]
                ),
            ]
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        $this->assertCount(0, $container);
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
