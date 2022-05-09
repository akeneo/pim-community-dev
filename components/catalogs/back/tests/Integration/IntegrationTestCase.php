<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration;

use Akeneo\Connectivity\Connection\PublicApi\Service\ConnectedAppFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

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

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $connectionCloser = self::getContainer()->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        $this->ensureKernelShutdown();
    }

    protected function logAs(string $username): void
    {
        $user = self::getContainer()->get('pim_user.repository.user')->findOneByIdentifier($username);
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        self::getContainer()->get('security.token_storage')->setToken($token);
    }

    protected function getAuthenticatedClient(array $scopes = []): KernelBrowser
    {
        $connectedAppFactory = self::getContainer()->get(ConnectedAppFactory::class);
        $connectedApp = $connectedAppFactory->createFakeConnectedAppWithValidToken(
            '11231759-a867-44b6-a36d-3ed7aeead51a',
            'shopifi',
            $scopes,
        );

        /** @var KernelBrowser $client */
        $client = self::getContainer()->get(KernelBrowser::class);

        $client->setServerParameter('AUTHORIZATION', 'Bearer ' . $connectedApp->getAccessToken());

        return $client;
    }

    protected function assertViolationsListContains(
        ConstraintViolationListInterface $violations,
        string $expectedMessage
    ): void {
        if (0 === $violations->count()) {
            throw new \LogicException('There is no violations');
        }

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            if ($expectedMessage === $violation->getMessage()) {
                return;
            }
        }

        throw new \LogicException(
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
}
