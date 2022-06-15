<?php

declare(strict_types=1);

namespace Akeneo\Test\Integration;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class TestCase extends KernelTestCase
{
    /** @var KernelInterface */
    protected $testKernel;

    /** @var CatalogInterface */
    protected $catalog;

    /**
     * @return Configuration
     */
    abstract protected function getConfiguration();

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->testKernel = static::bootKernel(['debug' => false]);
        $this->catalog = $this->get('akeneo_integration_tests.catalogs');

        if (null !== $this->getConfiguration()) {
            $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
            $fixturesLoader->load($this->getConfiguration());
        }

        // authentication should be done after loading the database as the user is created with first activated locale as default locale
        $authenticator = $this->get('akeneo_integration_tests.security.system_user_authenticator');
        $authenticator->createSystemUser();

        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        // Some messages can be in the queue after a failing test. To prevent error we remove then before each tests.
        $this->get('akeneo_integration_tests.launcher.job_launcher')->flushJobQueue();

        /** @var FilePersistedFeatureFlags $featureFlags*/
        $featureFlags = $this->get('feature_flags');
        $featureFlags->deleteFile();
    }

    /**
     * @param string $service
     *
     * @return mixed
     */
    protected function get(string $service)
    {
        return static::getContainer()->get($service);
    }

    /**
     * @param string $parameter
     *
     * @return mixed
     */
    protected function getParameter(string $parameter)
    {
        return static::getContainer()->getParameter($parameter);
    }

    /**
     * @param string $parameter
     *
     * @return bool
     */
    protected function hasParameter(string $parameter)
    {
        return static::getContainer()->hasParameter($parameter);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $connectionCloser = $this->get('akeneo_integration_tests.doctrine.connection.connection_closer');
        $connectionCloser->closeConnections();

        parent::tearDown();
    }

    /**
     * Look in every fixture directory if a fixture $name exists.
     * And return the pathname of the fixture if it exists.
     *
     * @param string $name
     *
     * @throws \Exception if no fixture $name has been found
     *
     * @return string
     */
    protected function getFixturePath(string $name): string
    {
        $configuration = $this->getConfiguration();
        foreach ($configuration->getFixtureDirectories() as $fixtureDirectory) {
            $path = $fixtureDirectory . DIRECTORY_SEPARATOR . $name;
            if (is_file($path) && false !== realpath($path)) {
                return realpath($path);
            }
        }

        throw new \Exception(sprintf('The fixture "%s" does not exist.', $name));
    }

    protected function getFileInfoKey(string $path): string
    {
        if (!is_file($path)) {
            throw new \Exception(sprintf('The path "%s" does not exist.', $path));
        }

        $fileStorer = $this->get('akeneo_file_storage.file_storage.file.file_storer');
        $fileInfo = $fileStorer->store(new \SplFileInfo($path), FileStorage::CATALOG_STORAGE_ALIAS);

        return $fileInfo->getKey();
    }

    /**
     * @param array $data
     *
     * @return CategoryInterface
     */
    protected function createCategory(array $data = []): CategoryInterface
    {
        $category = $this->get('pim_catalog.factory.category')->create();
        $this->get('pim_catalog.updater.category')->update($category, $data);
        $this->get('validator')->validate($category);
        $this->get('pim_catalog.saver.category')->save($category);

        return $category;
    }

    protected function createAdminUser(): UserInterface
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername('admin');
        $user->setPlainPassword('admin');
        $user->setEmail('admin@example.com');
        $user->setSalt('E1F53135E559C253');
        $user->setFirstName('John');
        $user->setLastName('Doe');

        $this->get('pim_user.manager')->updatePassword($user);

        $adminRole = $this->get('pim_user.repository.role')->findOneByIdentifier('ROLE_ADMINISTRATOR');
        if (null !== $adminRole) {
            $user->addRole($adminRole);
        }

        $userRole = $this->get('pim_user.repository.role')->findOneByIdentifier(User::ROLE_DEFAULT);
        if (null !== $userRole) {
            $user->removeRole($userRole);
        }

        $group = $this->get('pim_user.repository.group')->findOneByIdentifier('IT support');
        if (null !== $group) {
            $user->addGroup($group);
        }

        $this->get('validator')->validate($user);
        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }

    protected function createProductFromUserIntents(string $identifier, array $userIntents, int $userId = null): ProductInterface
    {
        if ($userId === null) {
            $userId = ($this->getUserId('peter') !== 0)
                ? $this->getUserId('peter')
                : $this->createUserWithRolesAndGroups('peter', ['ROLE_USER'], ['IT support'])->getId();
        }

        $command = UpsertProductCommand::createFromCollection(
            userId: $userId,
            productIdentifier: $identifier,
            userIntents: $userIntents
        );
        $this->messageBus->dispatch($command);
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        $this->getContainer()->get('pim_catalog.validator.unique_value_set')->reset();
        $this->clearDoctrineUoW();

        return $product;
    }

    protected function createUserWithRolesAndGroups(string $username, array $stringRoles, array $groupNames): UserInterface
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername($username);
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setPassword('password');
        $user->setEmail($username . '@example.com');

        $groups = $this->get('pim_user.repository.group')->findAll();
        foreach ($groups as $group) {
            if (\in_array($group->getName(), $groupNames)) {
                $user->addGroup($group);
            }
        }

        $roles = $this->get('pim_user.repository.role')->findAll();
        foreach ($roles as $role) {
            if (\in_array($role->getRole(), $stringRoles)) {
                $user->addRole($role);
            }
        }

        $violations = $this->get('validator')->validate($user);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }

    protected function createUserAllRoles(string $username, array $groups): User
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@example.com', uniqid()));
        $user->setPassword('fake');

        foreach ($groups as $group) {
            $user->addGroup($group);
        }

        $roles = $this->get('pim_user.repository.role')->findAll();
        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }

    protected function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        Assert::assertNotNull($id);

        return \intval($id);
    }

    protected function clearDoctrineUoW(): void
    {
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
    }
}
