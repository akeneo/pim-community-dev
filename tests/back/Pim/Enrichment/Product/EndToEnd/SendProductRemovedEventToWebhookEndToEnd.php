<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Enrichment\Product\EndToEnd;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\ProductLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\WebhookLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\MessageHandler\BusinessEventHandler;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductRemoved;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SendProductRemovedEventToWebhookEndToEnd extends ApiTestCase
{
    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var WebhookLoader */
    private $webhookLoader;

    /** @var ProductLoader */
    private $productLoader;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var IdentifiableObjectRepositoryInterface */
    private $userGroupRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->webhookLoader = $this->get('akeneo_connectivity.connection.fixtures.webhook_loader');
        $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');
        $this->normalizer = $this->get('pim_catalog.normalizer.standard.product');
        $this->userGroupRepository = $this->get('pim_user.repository.group');
    }

    public function test_that_a_connection_that_does_not_see_a_product_is_not_notified_about_its_removal(): void
    {
        $product = $this->productLoader->create(
            'product_not_viewable_by_redactor',
            [
                'categories' => ['categoryB'],
                'family' => 'familyA',
            ]
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
            $erpConnection->auditable()
        );

        /** @var HandlerStack $handlerStack*/
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(new MockHandler([
            new Response(200),
        ]));

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new ProductRemoved(
            'ecommerce',
            $this->normalizer->normalize($product, 'standard')
        );

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
