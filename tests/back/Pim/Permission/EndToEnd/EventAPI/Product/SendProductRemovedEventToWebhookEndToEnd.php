<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\EventAPI\Product;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\ProductLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\WebhookLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\MessageHandler\BusinessEventHandler;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

class SendProductRemovedEventToWebhookEndToEnd extends ApiTestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var WebhookLoader */
    private $webhookLoader;

    /** @var ProductLoader */
    private $productLoader;

    /** @var IdentifiableObjectRepositoryInterface */
    private $userGroupRepository;

    /** @var PermissionFixturesLoader */
    private $loader;

     protected function setUp(): void
     {
         parent::setUp();

         $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
         $this->webhookLoader = $this->get('akeneo_connectivity.connection.fixtures.webhook_loader');
         $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');
         $this->userGroupRepository = $this->get('pim_user.repository.group');
         $this->loader = $this->get('akeneo_integration_tests.loader.permissions');

         $this->loader->loadCategoriesAndAttributesForEventAPI();
     }

    public function test_that_a_connection_with_access_to_only_one_category_of_the_product_is_still_notified_about_its_removal(): void
    {
         $this->productLoader->create(
             'product_with_one_category_viewable_by_redactor_and_one_category_not_viewable_by_redactor',
             [
                 'categories' => ['view_category', 'category_without_right'],
                 'family' => 'familyA',
             ],
         );

         $erpConnection = $this->connectionLoader->createConnection('erp', 'erp', FlowType::DATA_SOURCE, false);
         $this->webhookLoader->initWebhook($erpConnection->code());
         $redactorGroup = $this->userGroupRepository->findOneByIdentifier('redactor');

         $this->connectionLoader->update(
             $erpConnection->code(),
             $erpConnection->label(),
             $erpConnection->flowType(),
             $erpConnection->image(),
             $erpConnection->userRoleId(),
             (string) $redactorGroup->getId(),
             $erpConnection->auditable(),
         );

         /** @var HandlerStack $handlerStack */
         $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
         $handlerStack->setHandler(new MockHandler([new Response(200)]));

         $container = [];
         $history = Middleware::history($container);
         $handlerStack->push($history);

         $message = new BulkEvent([
             new ProductRemoved(Author::fromNameAndType('ecommerce', 'ui'), [
                 'identifier' => 'product_with_one_category_viewable_by_redactor_and_one_category_not_viewable_by_redactor',
                 'category_codes' => ['view_category', 'category_without_right'],
                 ]
             )
         ]);

         /** @var $businessEventHandler BusinessEventHandler */
         $businessEventHandler = $this->get(BusinessEventHandler::class);
         $businessEventHandler->__invoke($message);

         $this->assertCount(1, $container);
    }

    public function test_that_a_connection_that_does_not_see_a_product_is_not_notified_about_its_removal(): void
    {
         $this->productLoader->create('product_not_viewable_by_redactor', [
             'categories' => ['category_without_right'],
             'family' => 'familyA',
         ]);
         $erpConnection = $this->connectionLoader->createConnection('erp', 'erp', FlowType::DATA_SOURCE, false);
         $this->webhookLoader->initWebhook($erpConnection->code());
         $redactorGroup = $this->userGroupRepository->findOneByIdentifier('redactor');
         $this->connectionLoader->update(
             $erpConnection->code(),
             $erpConnection->label(),
             $erpConnection->flowType(),
             $erpConnection->image(),
             $erpConnection->userRoleId(),
             (string) $redactorGroup->getId(),
             $erpConnection->auditable(),
         );

         /** @var HandlerStack $handlerStack */
         $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
         $handlerStack->setHandler(new MockHandler([new Response(200)]));

         $container = [];
         $history = Middleware::history($container);
         $handlerStack->push($history);

        $message = new BulkEvent([
            new ProductRemoved(Author::fromNameAndType('ecommerce', 'ui'), [
                'identifier' => 'product_not_viewable_by_redactor',
                'category_codes' => ['category_without_right'],
                ]
            )
        ]);

         /** @var $businessEventHandler BusinessEventHandler */
         $businessEventHandler = $this->get(BusinessEventHandler::class);
         $businessEventHandler->__invoke($message);

         $this->assertCount(0, $container);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
