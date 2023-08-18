<?php

declare(strict_types=1);

namespace Akeneo\Test\Integration;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\Test\IntegrationTestsBundle\Helper\ExperimentalTransactionHelper;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\HttpKernelBrowser;
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
        $this->testKernel = static::bootKernel(['debug' => (bool)($_SERVER['APP_DEBUG'] ?? false)]);

        /** @var FilePersistedFeatureFlags $featureFlags*/
        $featureFlags = $this->get('feature_flags');
        $featureFlags->deleteFile();

        $this->catalog = $this->get('akeneo_integration_tests.catalogs');

        self::getContainer()->get(ExperimentalTransactionHelper::class)->beginTransactions();

        if (null !== $this->getConfiguration()) {
            foreach ($this->getConfiguration()->getFeatureFlagsBeforeInstall() as $featureFlag) {
                $featureFlags->enable($featureFlag);
            }
            $fixturesLoader = $this->get('akeneo_integration_tests.loader.fixtures_loader');
            $fixturesLoader->load($this->getConfiguration());
        }

        // authentication should be done after loading the database as the user is created with first activated locale as default locale
        $authenticator = $this->get('akeneo_integration_tests.security.system_user_authenticator');
        $authenticator->createSystemUser();

        $this->get('pim_connector.doctrine.cache_clearer')->clear();

        // Some messages can be in the queue after a failing test. To prevent error we remove then before each tests.
        $this->get('akeneo_integration_tests.launcher.job_launcher')->flushJobQueue();
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
        self::getContainer()->get(ExperimentalTransactionHelper::class)->closeTransactions();

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

    protected function getProductUuid(string $productIdentifier): ?UuidInterface
    {
        $productUuid = $this->get('database_connection')->executeQuery(<<<SQL
SELECT BIN_TO_UUID(product_uuid) AS uuid
FROM pim_catalog_product_unique_data
INNER JOIN pim_catalog_attribute ON pim_catalog_product_unique_data.attribute_id = pim_catalog_attribute.id
WHERE raw_data = :identifier
AND pim_catalog_attribute.main_identifier = 1
SQL,
            ['identifier' => $productIdentifier]
        )->fetchOne();

        return $productUuid ? Uuid::fromString($productUuid) : null;
    }

    protected function getProductIdentifier(UuidInterface $uuid): ?string
    {
        return $this->get('database_connection')->executeQuery(<<<SQL
WITH main_identifier AS (
    SELECT id
    FROM pim_catalog_attribute
    WHERE main_identifier = 1
    LIMIT 1
)
SELECT raw_data AS identifier
FROM pim_catalog_product
LEFT JOIN pim_catalog_product_unique_data pcpud
    ON pcpud.product_uuid = pim_catalog_product.uuid 
    AND pcpud.attribute_id = (SELECT id FROM main_identifier)
WHERE uuid = :uuid
SQL,
            ['uuid' => $uuid->getBytes()]
        )->fetchOne();
    }

    protected function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        if (null === $id) {
            throw new \InvalidArgumentException(\sprintf('No user exists with username "%s"', $username));
        }

        return \intval($id);
    }

    protected function loginAs(string $username, ?HttpKernelBrowser $client = null): int
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn($username, $client);

        return $this->getUserId($username);
    }
}
