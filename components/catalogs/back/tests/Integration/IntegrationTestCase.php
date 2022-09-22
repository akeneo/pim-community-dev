<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration;

use Akeneo\Catalogs\Application\Persistence\GetLocalesQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogQuery;
use Akeneo\Catalogs\Test\Integration\Fakes\Clock;
use Akeneo\Catalogs\Test\Integration\Fakes\TimestampableSubscriber;
use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Connectivity\Connection\ServiceApi\Service\ConnectedAppFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\IntegrationTestsBundle\Helper\ExperimentalTransactionHelper;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
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
        self::getContainer()->set(
            'pim_catalog.event_subscriber.timestampable',
            new TimestampableSubscriber($this->clock)
        );
        self::getContainer()->set(
            'pim_versioning.event_subscriber.timestampable',
            new TimestampableSubscriber($this->clock)
        );

        self::getContainer()->get('pim_connector.doctrine.cache_clearer')->clear();

        self::getContainer()->get(ExperimentalTransactionHelper::class)->beginTransactions();
    }

    protected static function purgeData(): void
    {
        $fixturesLoader = self::getContainer()->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->purge();
    }

    protected function purgeDataAndLoadMinimalCatalog(): void
    {
        $catalog = self::getContainer()->get('akeneo_integration_tests.catalogs');
        $configuration = $catalog->useMinimalCatalog();
        $fixturesLoader = self::getContainer()->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($configuration);
    }

    protected function disableExperimentalTestDatabase(): void
    {
        self::getContainer()->get(ExperimentalTransactionHelper::class)->disable();
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
                        fn (ConstraintViolationInterface $violation) => $violation->getMessage(),
                        \iterator_to_array($violations)
                    )
                )
            )
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
    protected function createProduct(string $identifier, array $intents = [], ?int $userId = null): AbstractProduct
    {
        $bus = self::getContainer()->get('pim_enrich.product.message_bus');

        if (null === $userId) {
            $user = self::getContainer()->get('security.token_storage')->getToken()?->getUser();
            \assert($user instanceof UserInterface);
            $userId = $user->getId();
        }

        Assert::notNull($userId);

        $command = UpsertProductCommand::createWithIdentifier(
            $userId,
            ProductIdentifier::fromIdentifier($identifier),
            $intents
        );

        $bus->dispatch($command);

        self::getContainer()->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        return self::getContainer()->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    protected function createCatalog(string $id, string $name, string $ownerUsername): void
    {
        $commandBus = self::getContainer()->get(CommandBus::class);
        $commandBus->execute(new CreateCatalogCommand(
            $id,
            $name,
            $ownerUsername,
        ));
    }

    protected function enableCatalog(string $id): void
    {
        $connection = self::getContainer()->get(Connection::class);
        $connection->executeQuery(
            'UPDATE akeneo_catalog SET is_enabled = 1 WHERE id = :id',
            [
                'id' => Uuid::fromString($id)->getBytes(),
            ]
        );
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
            ]
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
            ]
        );
    }

    /**
     * @param array{
     *     code: string,
     *     type: string,
     *     available_locales?: array<string>,
     *     group?: string,
     *     scopable: bool,
     *     localizable: bool,
     *     options?: array<string>,
     * } $data
     */
    protected function createAttribute(array $data): void
    {
        $data = \array_merge([
            'group' => 'other',
        ], $data);

        $options = $data['options'] ?? [];
        unset($data['options']);

        $attribute = self::getContainer()->get('pim_catalog.factory.attribute')->create();
        self::getContainer()->get('pim_catalog.updater.attribute')->update($attribute, $data);
        self::getContainer()->get('pim_catalog.saver.attribute')->save($attribute);

        if ([] !== $options) {
            $this->createAttributeOptions($attribute, $options);
        }
    }

    private function createAttributeOptions(AttributeInterface $attribute, array $codes): void
    {
        $factory = self::getContainer()->get('pim_catalog.factory.attribute_option');
        $locales = \array_map(
            static fn ($locale) => $locale['code'],
            self::getContainer()->get(GetLocalesQueryInterface::class)->execute()
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

    protected function enableCurrency(string $code): void
    {
        $currency = self::getContainer()->get('pim_catalog.repository.currency')->findOneByIdentifier($code);
        self::getContainer()->get('pim_catalog.updater.currency')->update($currency, [
            'code' => $code,
            'enabled' => true,
        ]);
        self::getContainer()->get('pim_catalog.saver.currency')->save($currency);
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
}
