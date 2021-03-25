<?php
declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle\Import;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Repository\RoleRepositoryInterface;
use Box\Spout\Writer\WriterFactory;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class ImportUserRoleIntegration extends TestCase
{
    private const CSV_IMPORT_JOB_CODE = 'csv_user_role_import';
    private const XLSX_IMPORT_JOB_CODE = 'xlsx_user_role_import';

    private JobLauncher $jobLauncher;
    private RoleRepositoryInterface $roleRepository;
    private AclManager $aclManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->roleRepository = $this->get('pim_user.repository.role');
        $this->aclManager = $this->get('oro_security.acl.manager');

        $this->get(SqlCreateJobInstance::class)->createJobInstance([
            'code' => static::CSV_IMPORT_JOB_CODE,
            'label' => 'Test CSV',
            'job_name' => static::CSV_IMPORT_JOB_CODE,
            'status' => 0,
            'type' => 'import',
            'raw_parameters' => 'a:9:{s:8:"filePath";s:18:"/tmp/user_role.csv";s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:6:"escape";s:1:"\";s:10:"withHeader";b:1;s:13:"uploadAllowed";b:1;s:25:"invalid_items_file_format";s:3:"csv";s:14:"user_to_notify";N;s:21:"is_user_authenticated";b:0;}',
        ]);
        $this->get(SqlCreateJobInstance::class)->createJobInstance([
            'code' => static::XLSX_IMPORT_JOB_CODE,
            'label' => 'Test XLSX',
            'job_name' => static::XLSX_IMPORT_JOB_CODE,
            'status' => 0,
            'type' => 'import',
            'raw_parameters' => 'a:6:{s:8:"filePath";s:19:"/tmp/user_role.xlsx";s:10:"withHeader";b:1;s:13:"uploadAllowed";b:1;s:25:"invalid_items_file_format";s:4:"xlsx";s:14:"user_to_notify";N;s:21:"is_user_authenticated";b:0;}',
        ]);
    }

    /** @test */
    public function it_imports_user_roles_in_csv(): void
    {
        $csvContent = <<<CSV
role;label;permissions
ROLE_ADMINISTRATOR;Administrator;action:pim_enrich_product_create,action:pim_enrich_product_edit_attributes,action:pim_enrich_product_history,action:pim_enrich_product_index
ROLE_USER;"New user label";action:pim_enrich_product_index
ROLE_NEW;"No permission role";

CSV;
        $userRole = $this->roleRepository->findOneByIdentifier('ROLE_USER');
        self::assertNotNull($userRole);
        $userLabelBeforeImport = $userRole->getLabel();
        self::assertNull($this->roleRepository->findOneByIdentifier('ROLE_NEW'));
        $this->get('doctrine.orm.default_entity_manager')->clear();

        $this->jobLauncher->launchImport(static::CSV_IMPORT_JOB_CODE, $csvContent);
        $adminRole = $this->roleRepository->findOneByIdentifier('ROLE_ADMINISTRATOR');
        self::assertNotNull($adminRole);
        $this->assertRoleHasPermission($adminRole, 'action:pim_enrich_product_create');
        $this->assertRoleHasPermission($adminRole, 'action:pim_enrich_product_edit_attributes');
        $this->assertRoleHasPermission($adminRole, 'action:pim_enrich_product_history');
        $this->assertRoleHasPermission($adminRole, 'action:pim_enrich_product_index');

        $userRole = $this->roleRepository->findOneByIdentifier('ROLE_USER');
        self::assertNotNull($userRole);
        self::assertNotSame($userLabelBeforeImport, $userRole->getLabel());
        $this->assertRoleHasNotPermission($userRole, 'action:pim_enrich_product_create');
        $this->assertRoleHasNotPermission($userRole, 'action:pim_enrich_product_edit_attributes');
        $this->assertRoleHasNotPermission($userRole, 'action:pim_enrich_product_history');
        $this->assertRoleHasPermission($userRole, 'action:pim_enrich_product_index');

        $noPermissionRole = $this->roleRepository->findOneByIdentifier('ROLE_NEW');
        self::assertNotNull($noPermissionRole);
        self::assertSame('No permission role', $noPermissionRole->getLabel());
        $this->assertRoleHasNotPermission($noPermissionRole, 'action:pim_enrich_product_create');
        $this->assertRoleHasNotPermission($noPermissionRole, 'action:pim_enrich_product_edit_attributes');
        $this->assertRoleHasNotPermission($noPermissionRole, 'action:pim_enrich_product_history');
        $this->assertRoleHasNotPermission($noPermissionRole, 'action:pim_enrich_product_index');
    }

    /** @test */
    public function it_does_not_update_permissions_if_the_column_is_not_present()
    {
        $csvContent = <<<CSV
role;label
ROLE_ADMINISTRATOR;"New admin label"
ROLE_NEW;"No permission role"

CSV;

        $adminRole = $this->roleRepository->findOneByIdentifier('ROLE_ADMINISTRATOR');
        self::assertNotNull($adminRole);
        $this->assertRoleHasPermission($adminRole, 'action:pim_enrich_product_create');
        $this->assertRoleHasNotPermission($adminRole, 'action:pim_enrich_job_tracker_view_all_jobs');

        self::assertNull($this->roleRepository->findOneByIdentifier('ROLE_NEW'));
        $this->get('doctrine.orm.default_entity_manager')->clear();

        $this->jobLauncher->launchImport(static::CSV_IMPORT_JOB_CODE, $csvContent);
        $adminRole = $this->roleRepository->findOneByIdentifier('ROLE_ADMINISTRATOR');
        self::assertNotNull($adminRole);
        self::assertSame('New admin label', $adminRole->getLabel());
        $this->assertRoleHasPermission($adminRole, 'action:pim_enrich_product_create');
        $this->assertRoleHasNotPermission($adminRole, 'action:pim_enrich_job_tracker_view_all_jobs');

        $noPermissionRole = $this->roleRepository->findOneByIdentifier('ROLE_NEW');
        self::assertNotNull($noPermissionRole);
        self::assertSame('No permission role', $noPermissionRole->getLabel());
        $this->assertRoleHasNotPermission($noPermissionRole, 'action:pim_enrich_product_create');
        $this->assertRoleHasNotPermission($noPermissionRole, 'action:pim_enrich_product_edit_attributes');
        $this->assertRoleHasNotPermission($noPermissionRole, 'action:pim_enrich_product_history');
        $this->assertRoleHasNotPermission($noPermissionRole, 'action:pim_enrich_product_index');
    }

    /** @test */
    public function it_imports_user_roles_in_xlsx(): void
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'test_user_role_import');
        $writer = WriterFactory::create('xlsx');
        $writer->openToFile($temporaryFile);
        $writer->addRows([
            ['role', 'label', 'permissions'],
            ['ROLE_ADMINISTRATOR', 'Administrator', 'action:pim_enrich_product_create,action:pim_enrich_product_edit_attributes,action:pim_enrich_product_history,action:pim_enrich_product_index'],
            ['ROLE_USER', 'New user label', 'action:pim_enrich_product_index'],
            ['ROLE_NEW', 'No permission role', ''],
        ]);
        $writer->close();

        $userRole = $this->roleRepository->findOneByIdentifier('ROLE_USER');
        self::assertNotNull($userRole);
        $userLabelBeforeImport = $userRole->getLabel();
        self::assertNull($this->roleRepository->findOneByIdentifier('ROLE_NEW'));
        $this->get('doctrine.orm.default_entity_manager')->clear();

        $this->jobLauncher->launchImport(static::XLSX_IMPORT_JOB_CODE, file_get_contents($temporaryFile), null, [], [], 'xlsx');
        $adminRole = $this->roleRepository->findOneByIdentifier('ROLE_ADMINISTRATOR');
        self::assertNotNull($adminRole);
        $this->assertRoleHasPermission($adminRole, 'action:pim_enrich_product_create');
        $this->assertRoleHasPermission($adminRole, 'action:pim_enrich_product_edit_attributes');
        $this->assertRoleHasPermission($adminRole, 'action:pim_enrich_product_history');
        $this->assertRoleHasPermission($adminRole, 'action:pim_enrich_product_index');

        $userRole = $this->roleRepository->findOneByIdentifier('ROLE_USER');
        self::assertNotNull($userRole);
        self::assertNotSame($userLabelBeforeImport, $userRole->getLabel());
        $this->assertRoleHasNotPermission($userRole, 'action:pim_enrich_product_create');
        $this->assertRoleHasNotPermission($userRole, 'action:pim_enrich_product_edit_attributes');
        $this->assertRoleHasNotPermission($userRole, 'action:pim_enrich_product_history');
        $this->assertRoleHasPermission($userRole, 'action:pim_enrich_product_index');

        $noPermissionRole = $this->roleRepository->findOneByIdentifier('ROLE_NEW');
        self::assertNotNull($noPermissionRole);
        self::assertSame('No permission role', $noPermissionRole->getLabel());
        $this->assertRoleHasNotPermission($noPermissionRole, 'action:pim_enrich_product_create');
        $this->assertRoleHasNotPermission($noPermissionRole, 'action:pim_enrich_product_edit_attributes');
        $this->assertRoleHasNotPermission($noPermissionRole, 'action:pim_enrich_product_history');
        $this->assertRoleHasNotPermission($noPermissionRole, 'action:pim_enrich_product_index');
    }

    /** @test */
    public function it_fails_when_role_contains_lower_case(): void
    {
        $csvContent = <<<CSV
role;label;permissions
ROLE_NEWa;"No permission role";

CSV;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/role\.role: The role should begin with "ROLE_" and should contain only underscores and alphanumeric characters in uppercase.: ROLE_NEWa/');
        $this->jobLauncher->launchImport(static::CSV_IMPORT_JOB_CODE, $csvContent);
    }

    /** @test */
    public function it_fails_when_role_contains_space(): void
    {
        $csvContent = <<<CSV
role;label;permissions
ROLE_NEW WITH_SPACE;"No permission role";

CSV;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/role\.role: The role should begin with "ROLE_" and should contain only underscores and alphanumeric characters in uppercase.: ROLE_NEW WITH_SPACE/');
        $this->jobLauncher->launchImport(static::CSV_IMPORT_JOB_CODE, $csvContent);
    }

    /** @test */
    public function it_fails_when_role_contains_invalid_character(): void
    {
        $csvContent = <<<CSV
role;label;permissions
ROLE_NEW_(WITH_BRACKET;"No permission role";

CSV;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/role\.role: The role should begin with "ROLE_" and should contain only underscores and alphanumeric characters in uppercase.: ROLE_NEW_\(WITH_BRACKET/');
        $this->jobLauncher->launchImport(static::CSV_IMPORT_JOB_CODE, $csvContent);
    }

    private function assertRoleHasPermission(Role $role, string $permission): void
    {
        self::assertTrue($this->roleHasPermission($role, $permission), sprintf(
            'The \'%s\' permission is not set for \'%s\' role.',
            $permission,
            $role->getLabel()
        ));

    }

    private function assertRoleHasNotPermission(Role $role, string $permission): void
    {
        self::assertFalse($this->roleHasPermission($role, $permission), sprintf(
            'The \'%s\' permission is set for \'%s\' role.',
            $permission,
            $role->getLabel()
        ));
    }

    private function roleHasPermission(Role $role, string $permission): bool
    {
        $privileges = $this->aclManager->getPrivilegeRepository()->getPrivileges(
            $this->aclManager->getSid($role)
        );

        foreach ($privileges as $privilege) {
            if ($permission === $privilege->getIdentity()->getId()) {
                foreach ($privilege->getPermissions() as $permission) {
                    if (AccessLevel::NONE_LEVEL !== $permission->getAccessLevel()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
