<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration;

use Akeneo\Catalogs\ServiceAPI\Command\CreateCatalogCommand;
use Akeneo\Catalogs\ServiceAPI\Messenger\CommandBus;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Saver\ChannelSaverInterface;
use Akeneo\Connectivity\Connection\ServiceApi\Service\ConnectedAppFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractProduct;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\AttributeSaver;
use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Saver\FamilySaver;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class IntegrationTestCase extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel(['environment' => 'test', 'debug' => false]);

        self::getContainer()->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    protected function purgeData(): void
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

    protected function purgeDataAndLoadTechnicalCatalog(): void
    {
        $catalog = self::getContainer()->get('akeneo_integration_tests.catalogs');
        $configuration = $catalog->useTechnicalCatalog();
        $fixturesLoader = self::getContainer()->get('akeneo_integration_tests.loader.fixtures_loader');
        $fixturesLoader->load($configuration);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $connectionCloser = self::getContainer()->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        $this->ensureKernelShutdown();
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
        ?string $expectedPropertyPath = null,
    ): void {
        if (0 === $violations->count()) {
            $this->fail('There is no violations but expected at least one.');
        }

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            if ($expectedMessage === $violation->getMessage()
                && ($expectedPropertyPath === null || $violation->getPropertyPath() === $expectedPropertyPath)
            ) {
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
            'password' => \rand(),
            'first_name' => 'firstname_' . \rand(),
            'last_name' => 'lastname_' . \rand(),
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

        $command = UpsertProductCommand::createFromCollection($userId, $identifier, $intents);

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

    protected function createChannel(string $code, array $locales = []): void
    {
        /** @var SimpleFactoryInterface $channelFactory */
        $channelFactory = self::getContainer()->get('pim_catalog.factory.channel');
        /** @var ChannelInterface $channel */
        $channel = $channelFactory->create();

        /** @var ObjectUpdaterInterface $channelUpdater */
        $channelUpdater = self::getContainer()->get('pim_catalog.updater.channel');
        $channelUpdater->update($channel, [
            'code' => $code,
            'locales' => $locales,
            'currencies' => ['USD'],
            'category_tree' => 'master',
        ]);

        /** @var ValidatorInterface $validator */
        $validator = self::getContainer()->get('validator');
        $violations = $validator->validate($channel);
        self::assertSame(0, $violations->count(), (string) $violations);

        /** @var ChannelSaverInterface $channelSaver */
        $channelSaver = self::getContainer()->get('pim_catalog.saver.channel');
        $channelSaver->save($channel);
    }

    protected function createAttribute(array $attributeData): void
    {
        /** @var SimpleFactoryInterface $attributeFactory */
        $attributeFactory = self::getContainer()->get('pim_catalog.factory.attribute');
        /** @var AttributeInterface $attribute */
        $attribute = $attributeFactory->create();

        /** @var ObjectUpdaterInterface $attributeUpdater */
        $attributeUpdater = self::getContainer()->get('pim_catalog.updater.attribute');
        $attributeUpdater->update($attribute, $attributeData);

        /** @var ValidatorInterface $validator */
        $validator = self::getContainer()->get('validator');
        $violations = $validator->validate($attribute);
        self::assertSame(0, $violations->count(), (string) $violations);

        /** @var AttributeSaver $attributeSaver */
        $attributeSaver = self::getContainer()->get('pim_catalog.saver.attribute');
        $attributeSaver->save($attribute);
    }

    protected function createFamily(array $familyData): void
    {
        /** @var SimpleFactoryInterface $familyFactory */
        $familyFactory = self::getContainer()->get('pim_catalog.factory.family');
        /** @var FamilyInterface $family */
        $family = $familyFactory->create();

        /** @var ObjectUpdaterInterface $familyUpdater */
        $familyUpdater = self::getContainer()->get('pim_catalog.updater.family');
        $familyUpdater->update($family, $familyData);

        /** @var ValidatorInterface $validator */
        $validator = self::getContainer()->get('validator');
        $violations = $validator->validate($family);
        self::assertSame(0, $violations->count(), (string) $violations);

        /** @var FamilySaver $familySaver */
        $familySaver = self::getContainer()->get('pim_catalog.saver.family');
        $familySaver->save($family);
    }
}
