<?php

declare(strict_types=1);

namespace Akeneo\Test\Integration;

use Akeneo\Channel\Infrastructure\Doctrine\Repository\ChannelRepository;
use Akeneo\Channel\Infrastructure\Doctrine\Repository\LocaleRepository;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\CategorySaver;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Error\Normalizer\ConstraintViolationNormalizer;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\FileNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Validator\UniqueValuesSet;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\FilePersistedFeatureFlags;
use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifier;
use Akeneo\Test\IntegrationTestsBundle\Configuration\CatalogInterface;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\FileStorageBundle\Doctrine\ORM\Repository\FileInfoRepository;
use Akeneo\Tool\Bundle\MeasureBundle\Installer\MeasurementInstaller;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\BaseCachedObjectRepository;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

        self::getContainer()->set('pim_catalog.builder.product', new class() implements ProductBuilderInterface {
            public function createProduct($identifier = null, $familyCode = null) {
                throw new \Exception();
            }
            public function addOrReplaceValue(
                EntityWithValuesInterface $entityWithValues,
                AttributeInterface $attribute,
                ?string $localeCode,
                ?string $scopeCode,
                $data
            ): ValueInterface {
                throw new \Exception();
            }
        });
        self::getContainer()->set('pim_catalog.saver.product', new class() implements SaverInterface, BulkSaverInterface {
            public function save($object, array $options = []) {
                throw new \Exception();
            }
            public function saveAll(array $objects, array $options = []) {
                throw new \Exception();
            }
        });
        self::getContainer()->set('pim_catalog.updater.product', new class() implements ObjectUpdaterInterface {
            public function update($object, array $data, array $options = []) {
                throw new \Exception();
            }
        });
        self::getContainer()->set('pim_catalog.factory.product_model', new class() implements SimpleFactoryInterface {
            public function create() {
                throw new \Exception();
            }
        });
        self::getContainer()->set('pim_catalog.updater.product_model',new class() implements ObjectUpdaterInterface {
            public function update($object, array $data, array $options = []) {
                throw new \Exception();
            }
        });
        self::getContainer()->set('pim_catalog.saver.product_model', new class() implements SaverInterface, BulkSaverInterface {
            public function save($object, array $options = []) {
                throw new \Exception();
            }
            public function saveAll(array $objects, array $options = []) {
                throw new \Exception();
            }
        });
        self::getContainer()->set('pim_catalog.validator.product', $this->createMock(ValidatorInterface::class));
        self::getContainer()->set('pim_catalog.factory.category', $this->createMock(SimpleFactoryInterface::class));
        self::getContainer()->set('pim_catalog.updater.category', $this->createMock(SimpleFactoryInterface::class));
        self::getContainer()->set('pim_catalog.saver.category', $this->createMock(CategorySaver::class));
        self::getContainer()->set('pim_catalog.elasticsearch.indexer.product', $this->createMock(ProductIndexer::class));
        self::getContainer()->set('pim_catalog.elasticsearch.indexer.product_model', $this->createMock(ProductModelIndexer::class));
        self::getContainer()->set('akeneo_measure.installer.measurement_installer', $this->createMock(MeasurementInstaller::class));
        self::getContainer()->set('akeneo_integration_tests.launcher.job_launcher', $this->createMock(JobLauncher::class));
        self::getContainer()->set('pim_catalog.repository.locale', $this->createMock(LocaleRepository::class));
        self::getContainer()->set('pim_catalog.repository.channel', $this->createMock(ChannelRepository::class));
        self::getContainer()->set('pim_internal_api_serializer', self::getContainer()->get('serializer'));
        self::getContainer()->set('pim_enrich.normalizer.violation', $this->createMock(ConstraintViolationNormalizer::class));
        self::getContainer()->set('pim_catalog.localization.factory.number', $this->createMock(NumberFactory::class));
        self::getContainer()->set('pim_enrich.normalizer.file', $this->createMock(FileNormalizer::class));
        self::getContainer()->set('pim_notification.email.email_notifier', $this->createMock(MailNotifier::class));
        self::getContainer()->set('pim_enrich.repository.job_execution', $this->createMock(JobExecutionRepository::class));
        self::getContainer()->set('pim_datagrid.repository.datagrid_view', $this->createMock(DatagridViewRepository::class));
        self::getContainer()->set('pim_catalog.repository.cached_locale', $this->createMock(BaseCachedObjectRepository::class));
        self::getContainer()->set('pim_catalog.repository.cached_attribute', $this->createMock(BaseCachedObjectRepository::class));
        self::getContainer()->set('pim_catalog.repository.cached_category', $this->createMock(BaseCachedObjectRepository::class));
        self::getContainer()->set('pim_catalog.validator.unique_value_set', $this->createMock(UniqueValuesSet::class));

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
}
