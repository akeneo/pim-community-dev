<?php
declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle\Import;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\Connector\Writer\File\SpoutWriterFactory;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Akeneo\UserManagement\Domain\Permissions\MinimumEditRolePermission;
use Doctrine\DBAL\Connection;
use OpenSpout\Common\Entity\Row;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use PHPUnit\Framework\Assert;

final class ImportUserIntegration extends TestCase
{
    private const CSV_IMPORT_JOB_CODE = 'csv_user_import';
    private const XLSX_IMPORT_JOB_CODE = 'xlsx_user_import';

    private readonly JobLauncher $jobLauncher;
    private readonly UserRepositoryInterface $userRepository;
    private readonly UserLoader $userLoader;
    private readonly RoleWithPermissionsRepository $roleWithPermissionsRepository;
    private readonly AclManager $aclManager;
    private readonly UnitOfWorkAndRepositoriesClearer $cacheClearer;
    private readonly SimpleFactoryInterface $roleFactory;
    private readonly SaverInterface $roleSaver;
    private readonly RoleWithPermissionsSaver $roleWithPermissionsSaver;
    private readonly Connection $connection;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->userRepository = $this->get('pim_user.repository.user');
        $this->userLoader = $this->get(UserLoader::class);
        $this->roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        $this->aclManager = $this->get('oro_security.acl.manager');
        $this->cacheClearer = $this->get('pim_connector.doctrine.cache_clearer');
        $this->roleFactory = $this->get('pim_user.factory.role');
        $this->roleSaver = $this->get('pim_user.saver.role');
        $this->roleWithPermissionsSaver = $this->get('pim_user.saver.role_with_permissions');
        $this->connection = $this->get('database_connection');

        $this->get(SqlCreateJobInstance::class)->createJobInstance([
            'code' => self::CSV_IMPORT_JOB_CODE,
            'label' => 'Test CSV',
            'job_name' => self::CSV_IMPORT_JOB_CODE,
            'status' => 0,
            'type' => 'import',
            'raw_parameters' => 'a:5:{s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:6:"escape";s:1:"\";s:13:"uploadAllowed";b:1;s:25:"invalid_items_file_format";s:3:"csv";}',
        ]);
        $this->get(SqlCreateJobInstance::class)->createJobInstance([
            'code' => self::XLSX_IMPORT_JOB_CODE,
            'label' => 'Test XLSX',
            'job_name' => self::XLSX_IMPORT_JOB_CODE,
            'status' => 0,
            'type' => 'import',
            'raw_parameters' => 'a:2:{s:13:"uploadAllowed";b:1;s:25:"invalid_items_file_format";s:4:"xlsx";}',
        ]);
    }

    /** @test */
    public function it_imports_users_in_csv(): void
    {
        $csvContent = <<<CSV
        username;first_name;last_name;email;user_default_locale;timezone
        admin;John;Doe;admin@example.com;fr_FR;Europe/Paris
        new_user;James;Smith;new_user@example.com;en_US;
        CSV;
        $this->jobLauncher->launchImport(self::CSV_IMPORT_JOB_CODE, $csvContent);

        $admin = $this->userRepository->findOneByIdentifier('admin');
        Assert::assertSame('fr_FR', $admin->getUiLocale()->getCode());
        Assert::assertSame('Europe/Paris', $admin->getTimezone());

        $newUser = $this->userRepository->findOneByIdentifier('new_user');
        Assert::assertSame('James', $newUser->getFirstName());;
        Assert::assertSame('Smith', $newUser->getLastName());;
        Assert::assertSame('new_user@example.com', $newUser->getEmail());;
        Assert::assertSame('en_US', $newUser->getUiLocale()->getCode());
        Assert::assertSame('UTC', $newUser->getTimezone());
    }

    /** @test */
    public function it_imports_users_with_ignored_columns_in_csv(): void
    {
        $csvContent = <<<CSV
        username;first_name;last_name;email;user_default_locale;timezone;date_account_created;date_account_last_updated;last_logged_in;login_count
        admin;John;Doe;admin@example.com;fr_FR;Europe/Paris;2023-06-14T08:54:12+00:00;2023-06-14T08:56:34+00:00;2023-06-14T08:56:34+00:00;1
        new_user;James;Smith;new_user@example.com;en_US;;2023-06-14T08:54:12+00:00;2023-06-14T08:56:34+00:00;2023-06-14T08:56:34+00:00;1
        CSV;
        $this->jobLauncher->launchImport(self::CSV_IMPORT_JOB_CODE, $csvContent);

        $admin = $this->userRepository->findOneByIdentifier('admin');
        Assert::assertSame('fr_FR', $admin->getUiLocale()->getCode());
        Assert::assertSame('Europe/Paris', $admin->getTimezone());

        $newUser = $this->userRepository->findOneByIdentifier('new_user');
        Assert::assertSame('James', $newUser->getFirstName());;
        Assert::assertSame('Smith', $newUser->getLastName());;
        Assert::assertSame('new_user@example.com', $newUser->getEmail());;
        Assert::assertSame('en_US', $newUser->getUiLocale()->getCode());
        Assert::assertSame('UTC', $newUser->getTimezone());
    }

    /** @test */
    public function it_imports_user_groups_in_xlsx(): void
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'test_user_import');
        $writer = SpoutWriterFactory::create(SpoutWriterFactory::XLSX);
        $writer->openToFile($temporaryFile);
        $writer->addRows(
            \array_map(
                static fn (array $data): Row => Row::fromValues($data),
                [
                    ['username', 'email', 'enabled', 'first_name', 'last_name', 'groups', 'roles'],
                    ['new_user', 'new_user@example.com', '1', 'James', 'Smith', 'All', 'ROLE_USER'],
                ]
            )
        );
        $writer->close();

        $this->jobLauncher->launchImport(self::XLSX_IMPORT_JOB_CODE, file_get_contents($temporaryFile), null, [], [], 'xlsx');

        /** @var UserInterface $newUser */
        $newUser = $this->userRepository->findOneByIdentifier('new_user');
        Assert::assertSame('James', $newUser->getFirstName());;
        Assert::assertSame('Smith', $newUser->getLastName());;
        Assert::assertSame('new_user@example.com', $newUser->getEmail());;
        Assert::assertSame('en_US', $newUser->getUiLocale()->getCode());
        Assert::assertSame('UTC', $newUser->getTimezone());
        Assert::assertTrue($newUser->hasGroup('All'));
        Assert::assertTrue($newUser->hasRole('ROLE_USER'));
    }

    public function test_it_cannot_remove_last_edit_role_to_user(): void
    {
        $this->createRoleWithAcls('ROLE_WITH_EDIT_ROLE', MinimumEditRolePermission::getAllValues());
        $this->createRoleWithAcls('ROLE_WITHOUT_EDIT_ROLE', ['action:oro_config_system']);
        $this->userLoader->createUser('Julien', [], ['ROLE_USER', 'ROLE_WITH_EDIT_ROLE']);

        $csvContent = <<<CSV
        username;first_name;last_name;email;user_default_locale;timezone;roles
        admin;John;Doe;admin@example.com;fr_FR;Europe/Paris;ROLE_WITHOUT_EDIT_ROLE
        julien;Julien;Julien;julien@example.com;en_US;;ROLE_WITHOUT_EDIT_ROLE
        CSV;
        $this->jobLauncher->launchImport(self::CSV_IMPORT_JOB_CODE, $csvContent);

        /** @var UserInterface $admin */
        $admin = $this->userRepository->findOneByIdentifier('admin');
        Assert::assertSame('admin@example.com', $admin->getEmail());;
        Assert::assertSame('fr_FR', $admin->getUiLocale()->getCode());
        Assert::assertSame('Europe/Paris', $admin->getTimezone());
        Assert::assertSame(['ROLE_WITHOUT_EDIT_ROLE'], $admin->getRoles());

        /** @var UserInterface $julien */
        $julien = $this->userRepository->findOneByIdentifier('julien');
        Assert::assertSame('Julien@example.com', $julien->getEmail());;
        Assert::assertSame('en_US', $julien->getUiLocale()->getCode());
        Assert::assertSame(['ROLE_USER','ROLE_WITH_EDIT_ROLE'], $julien->getRoles());

        $this->assertWarning(
            '/pim_user.user.fields_errors.roles.last_user_with_edit_role_permissions/',
            self::CSV_IMPORT_JOB_CODE
        );
    }

    private function assertWarning(string $pattern, string $jobCode): void
    {
        $warnings = $this->connection->executeQuery(
            <<<SQL
            SELECT reason FROM akeneo_batch_warning warning
            INNER JOIN akeneo_batch_step_execution abse ON warning.step_execution_id = abse.id
            INNER JOIN akeneo_batch_job_execution abje ON abse.job_execution_id = abje.id    
            INNER JOIN akeneo_batch_job_instance abji ON abje.job_instance_id = abji.id
            WHERE abji.code = :jobCode
            SQL,
            ['jobCode' => $jobCode]
        )->fetchAllAssociative();
        Assert::assertCount(1, $warnings);
        Assert::assertMatchesRegularExpression($pattern, $warnings[0]['reason']);
    }


    private function createRoleWithAcls(string $roleCode, array $acls): void
    {
        $role = $this->roleFactory->create();
        $role->setRole($roleCode);
        $role->setLabel($roleCode);
        $this->roleSaver->save($role);

        $roleWithPermissions = $this->roleWithPermissionsRepository->findOneByIdentifier($roleCode);
        assert(null !== $roleWithPermissions);

        $permissions = $roleWithPermissions->permissions();
        foreach ($acls as $acl) {
            $permissions[$acl] = true;
        }
        $roleWithPermissions->setPermissions($permissions);

        $this->roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

        $this->aclManager->flush();
        $this->aclManager->clearCache();
        $this->cacheClearer->clear();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
