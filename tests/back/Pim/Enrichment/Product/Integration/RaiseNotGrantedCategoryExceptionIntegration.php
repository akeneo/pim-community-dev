<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Enrichment\Product\Integration;

use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\ConnectionLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\FamilyVariantLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\ProductLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Enrichment\ProductModelLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Structure\AttributeLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\Structure\FamilyLoader;
use Akeneo\Connectivity\Connection\back\tests\Integration\Fixtures\WebhookLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\MessageHandler\BusinessEventHandler;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\NotGrantedCategoryException;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\Webhook\EventDataBuilderInterface;
use Akeneo\SharedCatalog\tests\back\Utils\AuthenticateAs;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RaiseNotGrantedCategoryExceptionIntegration extends ApiTestCase
{
    use AuthenticateAs;

    /** @var ConnectionLoader */
    private $connectionLoader;

    /** @var ProductLoader */
    private $productLoader;

    /** @var ProductModelLoader */
    private $productModelLoader;

    /** @var FamilyLoader */
    private $familyLoader;

    /** @var FamilyVariantLoader */
    private $familyVariantLoader;

    /** @var AttributeLoader */
    private $attributeLoader;

    /** @var IdentifiableObjectRepositoryInterface */
    private $userGroupRepository;

    /** @var NormalizerInterface */
    private $productNormalizer;

    /** @var NormalizerInterface */
    private $productModelNormalizer;

    /** @var WebhookLoader */
    private $webhookLoader;

    /** @var EventDataBuilderInterface */
    private $productCreatedAndUpdatedEventDataBuilder;

    /** @var EventDataBuilderInterface */
    private $productModelCreatedAndUpdatedEventDataBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->productLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product');
        $this->productModelLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.product_model');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
        $this->familyLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.family');
        $this->familyVariantLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.family_variant');
        $this->attributeLoader = $this->get('akeneo_connectivity.connection.fixtures.structure.attribute');
        $this->userGroupRepository = $this->get('pim_user.repository.group');
        $this->productNormalizer = $this->get('pim_catalog.normalizer.standard.product');
        $this->productModelNormalizer = $this->get('pim_catalog.normalizer.standard.product_model');
        $this->webhookLoader = $this->get('akeneo_connectivity.connection.fixtures.webhook_loader');
        $this->productCreatedAndUpdatedEventDataBuilder = $this->get(
            'pim_catalog.webhook.event_data_builder.product_created_and_updated'
        );
        $this->productModelCreatedAndUpdatedEventDataBuilder = $this->get(
            'pim_catalog.webhook.event_data_builder.product_model_created_and_updated'
        );
    }

    public function test_that_the_exception_is_raised_when_trying_to_build_product_data_builder(): void
    {
        $product = $this->productLoader->create(
            'product_not_viewable_by_redactor',
            [
                'categories' => ['categoryB'],
                'family' => 'familyA',
            ]
        );

        $this->expectException(NotGrantedCategoryException::class);
        $this->authenticateAs('mary');
        $this->productCreatedAndUpdatedEventDataBuilder->build(
            new ProductUpdated(
                Author::fromNameAndType('mary', 'ui'),
                $this->productNormalizer->normalize($product, 'standard')
            )
        );
    }

    public function test_that_the_exception_is_raised_when_trying_to_build_product_model_data_builder(): void
    {
        $productModel = $this->loadProductModel();

        $this->expectException(NotGrantedCategoryException::class);
        $this->authenticateAs('mary');
        $this->productModelCreatedAndUpdatedEventDataBuilder->build(
            new ProductModelUpdated(
                Author::fromNameAndType('mary', 'ui'),
                $this->productModelNormalizer->normalize($productModel, 'standard')
            )
        );
    }

    public function test_that_that_no_webhook_is_sent_when_a_connection_tries_to_update_non_authorized_product(): void
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
            (string)$redactorGroup->getId(),
            $erpConnection->auditable()
        );

        /** @var HandlerStack $handlerStack */
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(
            new MockHandler(
                [
                    new Response(200),
                ]
            )
        );

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new ProductUpdated(
            Author::fromNameAndType('mary', 'ui'),
            $this->productNormalizer->normalize($product, 'standard')
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        $this->assertCount(0, $container);
    }

    public function test_that_that_no_webhook_is_sent_when_a_connection_tries_to_update_non_authorized_product_model(): void
    {
        $productModel = $this->loadProductModel();
        $erpConnection = $this->connectionLoader->createConnection('erp', 'erp', FlowType::DATA_SOURCE, false);
        $this->webhookLoader->initWebhook($erpConnection->code());
        $redactorGroup = $this->userGroupRepository->findOneByIdentifier('redactor');

        $this->connectionLoader->update(
            $erpConnection->code(),
            $erpConnection->label(),
            $erpConnection->flowType(),
            $erpConnection->image(),
            $erpConnection->userRoleId(),
            (string)$redactorGroup->getId(),
            $erpConnection->auditable()
        );

        /** @var HandlerStack $handlerStack */
        $handlerStack = $this->get('akeneo_connectivity.connection.webhook.guzzle_handler');
        $handlerStack->setHandler(
            new MockHandler(
                [
                    new Response(200),
                ]
            )
        );

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $message = new ProductModelUpdated(
            Author::fromNameAndType('mary', 'ui'),
            $this->productModelNormalizer->normalize($productModel, 'standard')
        );

        /** @var $businessEventHandler BusinessEventHandler */
        $businessEventHandler = $this->get(BusinessEventHandler::class);
        $businessEventHandler->__invoke($message);

        $this->assertCount(0, $container);
    }

    /**
     * @throws \Exception
     */
    private function loadProductModel(): ProductModelInterface
    {
        $this->attributeLoader->create(
            [
                'code' => 'a_text_attribute',
                'type' => 'pim_catalog_text',
            ]
        );

        $this->familyLoader->create(
            [
                'code' => 'a_family',
                'attributes' => ['a_text_attribute', 'a_yes_no'],
            ]
        );

        $familyVariant = $this->familyVariantLoader->create(
            [
                'code' => 'a_family_variant',
                'family' => 'a_family',
                'variant_attribute_sets' => [
                    [
                        'axes' => ['a_yes_no'],
                        'attributes' => [],
                        'level' => 1,
                    ],
                ],
            ]
        );

        return $this->productModelLoader->create(
            ['code' => 'a_product_model', 'family_variant' => $familyVariant->getCode(), 'categories' => ['categoryB']]
        );
    }

    /**
     * @return \Akeneo\Test\Integration\Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
