<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration;

use Akeneo\Catalogs\Application\Persistence\Locale\GetLocalesQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Command\UpdateProductMappingSchemaCommand;
use Akeneo\Catalogs\ServiceAPI\Events\InvalidCatalogDisabledEvent;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Catalogs\Test\Integration\Fakes\Clock;
use Akeneo\Catalogs\Test\Integration\Fakes\TimestampableSubscriber;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Connectivity\Connection\ServiceApi\Service\ConnectedAppFactory;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\Test\IntegrationTestsBundle\Helper\ExperimentalTransactionHelper;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\StorageAttributes;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException as DependencyInjectionInvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class IntegrationTestCase extends WebTestCase
{
    protected ?Clock $clock;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel(['environment' => 'test', 'debug' => false]);

        $this->clock = new Clock();

        // When the container cache does not exist (var/cache is empty for example),
        // Symfony builds the container when the kernel is booted for the first time.
        // During the first build of the container, some services, like the listeners, are initialized.
        // Trying to override a service already initialized is forbidden.
        // The alternative solution of overriding services in the configuration is not appliable to us, doing so
        // would affect ALL tests of all contexts in the PIM.
        // Instead, the dirty but working solution is to catch this error, reboot the kernel and retry.
        // It works because on the second boot, the container is already cached, no services are initialized.
        try {
            $this->overrideServices();
        } catch (DependencyInjectionInvalidArgumentException) {
            static::bootKernel(['environment' => 'test', 'debug' => false]);
            $this->overrideServices();
        }

        self::getContainer()->get('pim_connector.doctrine.cache_clearer')->clear();
        self::getContainer()->get(ExperimentalTransactionHelper::class)->beginTransactions();

        self::getContainer()->get(FeatureFlags::class)->enable('catalogs');
    }

    protected function overrideServices(): void
    {
        self::getContainer()->set(
            'pim_catalog.event_subscriber.timestampable',
            new TimestampableSubscriber($this->clock),
        );
        self::getContainer()->set(
            'pim_versioning.event_subscriber.timestampable',
            new TimestampableSubscriber($this->clock),
        );
    }

    protected static function purgeData(): void
    {
        self::resetCatalogMappingFilesystem();
        $fixturesLoader = self::getContainer()->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->purge();
    }

    protected static function resetCatalogMappingFilesystem(): void
    {
        /** @var Filesystem $catalogMappingFilesystem */
        $catalogMappingFilesystem = self::getContainer()->get('oneup_flysystem.catalogs_mapping_filesystem');

        $paths = $catalogMappingFilesystem->listContents('/')->filter(
            fn (StorageAttributes $attributes): bool => $attributes instanceof FileAttributes,
        )->map(
            fn (FileAttributes $attributes): string => $attributes->path(),
        );

        foreach ($paths as $path) {
            $catalogMappingFilesystem->delete($path);
        }
    }

    protected function purgeDataAndLoadMinimalCatalog(): void
    {
        $catalog = self::getContainer()->get('akeneo_integration_tests.catalogs');
        $configuration = $catalog->useMinimalCatalog();
        self::resetCatalogMappingFilesystem();
        $fixturesLoader = self::getContainer()->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($configuration);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        self::getContainer()->get(ExperimentalTransactionHelper::class)->closeTransactions();

        $connectionCloser = self::getContainer()->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        $this->ensureKernelShutdown();

        parent::tearDown();
    }

    protected function disableExperimentalTestDatabase(): void
    {
        self::getContainer()->get(ExperimentalTransactionHelper::class)->disable();
    }

    protected function logAs(string $username): TokenInterface
    {
        $user = self::getContainer()->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::notNull($user);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        self::getContainer()->get('security.token_storage')->setToken($token);

        return $token;
    }

    protected function getAuthenticatedPublicApiClient(array $scopes = []): KernelBrowser
    {
        $connectedAppFactory = self::getContainer()->get(ConnectedAppFactory::class);
        $connectedApp = $connectedAppFactory->createFakeConnectedAppWithValidToken(
            '11231759-a867-44b6-a36d-3ed7aeead51a',
            'shopifi',
            $scopes,
        );

        $this->addAllPermissionsUserGroup('app_shopifi');

        /** @var KernelBrowser $client */
        $client = self::getContainer()->get(KernelBrowser::class);
        $client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer ' . $connectedApp->getAccessToken());

        // The connected user is not derivated from the access token when in `test` env
        // We need to explicitly log in with it
        $this->logAs($connectedApp->getUsername());

        return $client;
    }

    protected function getAuthenticatedInternalApiClient(string $username = 'admin'): KernelBrowser
    {
        /** @var KernelBrowser $client */
        $client = self::getContainer()->get(KernelBrowser::class);

        $this->createUser($username);
        $token = $this->logAs($username);

        $session = self::getContainer()->get('session');
        $session->set('_security_main', \serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        return $client;
    }

    private function addAllPermissionsUserGroup(string $group): void
    {
        $this->callPermissionsSaver(
            service: 'Akeneo\Pim\Permission\Bundle\Saver\UserGroupAttributeGroupPermissionsSaver',
            group: $group,
            permissions: [
                'edit' => [
                    'all' => true,
                    'identifiers' => [],
                ],
                'view' => [
                    'all' => true,
                    'identifiers' => [],
                ],
            ],
        );
        $this->callPermissionsSaver(
            service: 'Akeneo\Pim\Permission\Bundle\Saver\UserGroupLocalePermissionsSaver',
            group: $group,
            permissions: [
                'edit' => [
                    'all' => true,
                    'identifiers' => [],
                ],
                'view' => [
                    'all' => true,
                    'identifiers' => [],
                ],
            ],
        );
        $this->callPermissionsSaver(
            service: 'Akeneo\Pim\Permission\Bundle\Saver\UserGroupCategoryPermissionsSaver',
            group: $group,
            permissions: [
                'own' => [
                    'all' => true,
                    'identifiers' => [],
                ],
                'edit' => [
                    'all' => true,
                    'identifiers' => [],
                ],
                'view' => [
                    'all' => true,
                    'identifiers' => [],
                ],
            ],
        );
    }

    private function callPermissionsSaver(string $service, string $group, array $permissions): void
    {
        if (self::getContainer()->has($service)) {
            self::getContainer()->get($service)->save($group, $permissions);
        }
    }

    protected function assertViolationsListContains(
        ConstraintViolationListInterface $violations,
        string $expectedMessage,
    ): void {
        $this->addToAssertionCount(1);

        if (0 === $violations->count()) {
            $this->fail('There is no violations but expected at least one.');
        }

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            if ($expectedMessage === $violation->getMessage()) {
                return;
            }
        }

        $this->fail(
            \sprintf(
                'Violation with message "%s" not found, got "%s"',
                $expectedMessage,
                \implode(
                    '","',
                    \array_map(
                        fn (ConstraintViolationInterface $violation): string|\Stringable => $violation->getMessage(),
                        \iterator_to_array($violations),
                    ),
                ),
            ),
        );
    }

    protected function createUser(string $username, ?array $groups = null, ?array $roles = null): UserInterface
    {
        $userPayload = [
            'username' => $username,
            'password' => \random_int(0, \mt_getrandmax()),
            'first_name' => 'firstname_' . \random_int(0, \mt_getrandmax()),
            'last_name' => 'lastname_' . \random_int(0, \mt_getrandmax()),
            'email' => \sprintf('%s@example.com', $username),
        ];

        if (null !== $groups) {
            $userPayload['groups'] = $groups;
        }

        if (null !== $roles) {
            $userPayload['roles'] = $roles;
        }

        $user = self::getContainer()->get('pim_user.factory.user')->create();
        self::getContainer()->get('pim_user.updater.user')->update($user, $userPayload);

        $violations = self::getContainer()->get('validator')->validate($user);
        Assert::count($violations, 0);

        self::getContainer()->get('pim_user.saver.user')->save($user);

        return $user;
    }

    /**
     * @param array<UserIntent> $intents
     */
    protected function createProduct(string | UuidInterface $identifier, array $intents = [], ?int $userId = null): AbstractProduct
    {
        $bus = self::getContainer()->get('pim_enrich.product.message_bus');

        if (null === $userId) {
            $user = self::getContainer()->get('security.token_storage')->getToken()?->getUser();
            \assert($user instanceof UserInterface);
            $userId = $user->getId();
        }

        Assert::notNull($userId);

        $command = \is_string($identifier) ?
            UpsertProductCommand::createWithIdentifier(
                $userId,
                ProductIdentifier::fromIdentifier($identifier),
                $intents,
            ) :
            UpsertProductCommand::createWithUuid(
                $userId,
                ProductUuid::fromUuid($identifier),
                \array_merge(
                    [
                        new SetIdentifierValue('sku', $identifier->toString()),
                    ],
                    $intents,
                ),
            );

        $bus->dispatch($command);

        self::getContainer()->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return self::getContainer()->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    protected function createCatalog(
        string $id,
        string $name,
        string $ownerUsername,
        bool $isEnabled = true,
        ?array $catalogProductSelection = null,
        ?array $catalogProductValueFilters = null,
        ?string $productMappingSchema = null,
        ?array $catalogProductMapping = null,
    ): void {
        $commandBus = self::getContainer()->get(CommandBus::class);
        $commandBus->execute(new CreateCatalogCommand(
            $id,
            $name,
            $ownerUsername,
        ));
        if ($isEnabled) {
            $this->enableCatalog($id);
        }
        if ($catalogProductSelection !== null) {
            $this->setCatalogProductSelection($id, $catalogProductSelection);
        }
        if ($catalogProductValueFilters !== null) {
            $this->setCatalogProductValueFilters($id, $catalogProductValueFilters);
        }
        if ($productMappingSchema !== null) {
            $this->setProductMappingSchema($id, $productMappingSchema);
        }
        if ($catalogProductMapping !== null) {
            $this->setCatalogProductMapping($id, $catalogProductMapping);
        }
    }

    protected function enableCatalog(string $id): void
    {
        $connection = self::getContainer()->get(Connection::class);
        $connection->executeQuery(
            'UPDATE akeneo_catalog SET is_enabled = 1 WHERE id = :id',
            [
                'id' => Uuid::fromString($id)->getBytes(),
            ],
        );
    }

    protected function setProductMappingSchema(string $id, string $productMappingSchema): void
    {
        $commandBus = self::getContainer()->get(CommandBus::class);
        $commandBus->execute(new UpdateProductMappingSchemaCommand(
            $id,
            \json_decode($productMappingSchema, false, 512, JSON_THROW_ON_ERROR),
        ));
    }

    protected function setCatalogProductSelection(string $id, array $criteria)
    {
        $connection = self::getContainer()->get(Connection::class);
        $connection->executeQuery(
            'UPDATE akeneo_catalog SET product_selection_criteria = :criteria WHERE id = :id',
            [
                'id' => Uuid::fromString($id)->getBytes(),
                'criteria' => \array_values($criteria),
            ],
            [
                'criteria' => Types::JSON,
            ],
        );
    }

    protected function setCatalogProductValueFilters(string $id, array $filters)
    {
        $connection = self::getContainer()->get(Connection::class);
        $connection->executeQuery(
            'UPDATE akeneo_catalog SET product_value_filters = :filters WHERE id = :id',
            [
                'id' => Uuid::fromString($id)->getBytes(),
                'filters' => $filters,
            ],
            [
                'filters' => Types::JSON,
            ],
        );
    }

    protected function setCatalogProductMapping(string $id, array $productMapping)
    {
        $connection = self::getContainer()->get(Connection::class);
        $connection->executeQuery(
            'UPDATE akeneo_catalog SET product_mapping = :productMapping WHERE id = :id',
            [
                'id' => Uuid::fromString($id)->getBytes(),
                'productMapping' => $productMapping,
            ],
            [
                'productMapping' => Types::JSON,
            ],
        );
    }

    /**
     * @param array{
     *     code: string,
     *     type: string,
     *     available_locales?: array<string>,
     *     group?: string,
     *     scopable?: bool,
     *     localizable?: bool,
     *     options?: array<string>,
     * } $data
     */
    protected function createAttribute(array $data): void
    {
        $data['group'] ??= 'other';

        $options = $data['options'] ?? [];
        unset($data['options']);

        $attribute = self::getContainer()->get('pim_catalog.factory.attribute')->create();
        self::getContainer()->get('pim_catalog.updater.attribute')->update($attribute, $data);
        self::getContainer()->get('pim_catalog.saver.attribute')->save($attribute);

        if ([] !== $options) {
            $this->createAttributeOptions($attribute, $options);
        }

        /**
         * The AbstractAttribute model is stateful and the getTranslation rely on an internal $locale to returns the
         * translation. When you update the translation, the AbstractAttribute keeps in memory the last locale you
         * updated.
         * As Doctrine keeps in UOW objects, when you search for an attribute it translates the label according to the
         * last locale you updated.
         * If in your test you update the en_US, then fr_FR, you'll have the attribute label automatically translated
         * in french no matter what you asked.
         * Clearing the UOW allows us to have a clean attribute during the Doctrine hydration and the good translation.
         */
        self::getContainer()->get('pim_connector.doctrine.cache_clearer')->clear();
    }
    protected function removeAttribute(string $code): void
    {
        $attribute = self::getContainer()->get('pim_catalog.repository.attribute')
            ->findOneByIdentifier($code);

        self::getContainer()->get('pim_catalog.remover.attribute')->remove($attribute);
        $this->waitForQueuedJobs();
    }

    private function createAttributeOptions(AttributeInterface $attribute, array $codes): void
    {
        $factory = self::getContainer()->get('pim_catalog.factory.attribute_option');
        $locales = \array_map(
            static fn ($locale) => $locale['code'],
            self::getContainer()->get(GetLocalesQueryInterface::class)->execute(),
        );

        $options = [];

        foreach ($codes as $i => $code) {
            /** @var AttributeOptionInterface $option */
            $option = $factory->create();
            $option->setCode(\strtolower(\trim(\preg_replace('/[^A-Za-z0-9-]+/', '_', $code))));
            $option->setAttribute($attribute);
            $option->setSortOrder($i);

            foreach ($locales as $locale) {
                $value = new AttributeOptionValue();
                $value->setOption($option);
                $value->setLocale($locale);
                $value->setLabel($code);

                $option->addOptionValue($value);
            }

            $options[] = $option;
        }

        self::getContainer()->get('pim_catalog.saver.attribute_option')->saveAll($options);
    }

    /**
     * @param array{
     *     code: string,
     *     sort_order?: integer,
     *     attributes?: array<string>,
     *     labels?: array<string, string>,
     * } $data
     */
    protected function createAttributeGroup(array $data): void
    {
        $attributeGroup = self::getContainer()->get('pim_catalog.repository.attribute_group')
            ->findOneByIdentifier($data['code']);

        if (null === $attributeGroup) {
            $attributeGroup = self::getContainer()->get('pim_catalog.factory.attribute_group')->create();
        }

        /** @var AttributeGroupInterface $attributeGroup */
        self::getContainer()->get('pim_catalog.updater.attribute_group')->update($attributeGroup, $data);
        self::getContainer()->get('pim_catalog.saver.attribute_group')->save($attributeGroup);
    }

    protected function createChannel(string $code, array $locales = [], array $currencies = ['USD']): void
    {
        /** @var ChannelInterface $channel */
        $channel = self::getContainer()->get('pim_catalog.factory.channel')->create();
        self::getContainer()->get('pim_catalog.updater.channel')->update($channel, [
            'code' => $code,
            'locales' => $locales,
            'currencies' => $currencies,
            'category_tree' => 'master',
        ]);
        self::getContainer()->get('pim_catalog.saver.channel')->save($channel);
    }

    protected function removeChannel(string $code): void
    {
        $channel = self::getContainer()->get('pim_catalog.repository.channel')
            ->findOneByIdentifier($code);

        self::getContainer()->get('pim_catalog.remover.channel')->remove($channel);
    }

    protected function createFamily(array $familyData): void
    {
        /** @var FamilyInterface $family */
        $family = self::getContainer()->get('pim_catalog.factory.family')->create();
        self::getContainer()->get('pim_catalog.updater.family')->update($family, $familyData);
        self::getContainer()->get('pim_catalog.saver.family')->save($family);
    }

    protected function createCategory(array $data = []): void
    {
        /** @var CategoryInterface $category */
        $category = self::getContainer()->get('pim_catalog.factory.category')->create();
        self::getContainer()->get('pim_catalog.updater.category')->update($category, $data);
        self::getContainer()->get('pim_catalog.saver.category')->save($category);
    }

    protected function createGroup(array $data = []): void
    {
        /** @var GroupInterface $group */
        $group = self::getContainer()->get('pim_catalog.factory.group')->create();
        self::getContainer()->get('pim_catalog.updater.group')->update($group, $data);
        self::getContainer()->get('pim_catalog.saver.group')->save($group);
    }

    protected function createGroupType(array $data = []): void
    {
        /** @var GroupInterface $groupType */
        $groupType = self::getContainer()->get('pim_catalog.factory.group_type')->create();
        self::getContainer()->get('pim_catalog.updater.group_type')->update($groupType, $data);
        self::getContainer()->get('pim_catalog.saver.group_type')->save($groupType);
    }

    protected function enableCurrency(string $code): void
    {
        $currency = self::getContainer()->get('pim_catalog.repository.currency')->findOneByIdentifier($code);
        self::getContainer()->get('pim_catalog.updater.currency')->update($currency, [
            'code' => $code,
            'enabled' => true,
        ]);
        self::getContainer()->get('pim_catalog.saver.currency')->save($currency);
    }

    protected function disableCurrency(string $code): void
    {
        $currency = self::getContainer()->get('pim_catalog.repository.currency')->findOneByIdentifier($code);
        self::getContainer()->get('pim_catalog.updater.currency')->update($currency, [
            'code' => $code,
            'enabled' => false,
        ]);
        self::getContainer()->get('pim_catalog.saver.currency')->save($currency);
    }

    protected function disableLocale(string $code): void
    {
        $locale = self::getContainer()->get('pim_catalog.repository.locale')->findOneByIdentifier($code);
        self::getContainer()->get('pim_catalog.updater.locale')->update($locale, [
            'code' => $code,
            'enabled' => false,
        ]);
        self::getContainer()->get('pim_catalog.saver.locale')->save($locale);
    }

    protected function getCatalog(string $id): Catalog
    {
        /** @var ?Catalog $catalog */
        $catalog = self::getContainer()->get(QueryBus::class)->execute(new GetCatalogQuery($id));
        $this->assertNotNull($catalog);

        return $catalog;
    }

    protected function removeAttributeOption(string $code): void
    {
        $attributeOption = self::getContainer()->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier($code);

        self::getContainer()->get('pim_catalog.remover.attribute_option')->remove($attributeOption);
        $this->waitForQueuedJobs();
    }

    protected function waitForQueuedJobs(): void
    {
        self::getContainer()->get('akeneo_integration_tests.launcher.job_launcher')->launchConsumerUntilQueueIsEmpty();
    }

    protected function addSubscriberForReadProductEvent(\Closure $onEventCallback): void
    {
        $subscriber = new class($onEventCallback) implements EventSubscriberInterface {
            public function __construct(private readonly \Closure $closure)
            {
            }

            public static function getSubscribedEvents(): array
            {
                return [ReadProductsEvent::class => 'onReadProductsEvent'];
            }

            public function onReadProductsEvent(ReadProductsEvent $event): void
            {
                ($this->closure)($event->getCount());
            }
        };

        self::getContainer()->get('event_dispatcher')->addSubscriber($subscriber);
    }

    protected function addSubscriberForInvalidCatalogDisabledEvent(\Closure $onEventCallback): void
    {
        $subscriber = new class($onEventCallback) implements EventSubscriberInterface {
            public function __construct(private readonly \Closure $closure)
            {
            }

            public static function getSubscribedEvents(): array
            {
                return [InvalidCatalogDisabledEvent::class => 'onEvent'];
            }

            public function onEvent(InvalidCatalogDisabledEvent $event): void
            {
                ($this->closure)($event->getCatalogId());
            }
        };

        self::getContainer()->get('event_dispatcher')->addSubscriber($subscriber);
    }
}
