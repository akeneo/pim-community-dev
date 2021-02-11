<?php
declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle\Import;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use Box\Spout\Writer\WriterFactory;
use PHPUnit\Framework\Assert;

final class ImportUserIntegration extends TestCase
{
    private const CSV_IMPORT_JOB_CODE = 'csv_user_import';
    private const XLSX_IMPORT_JOB_CODE = 'xlsx_user_import';

    private JobLauncher $jobLauncher;
    private UserRepositoryInterface $userRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->userRepository = $this->get('pim_user.repository.user');

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
    public function it_imports_user_groups_in_xlsx(): void
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'test_user_import');
        $writer = WriterFactory::create('xlsx');
        $writer->openToFile($temporaryFile);
        $writer->addRows([
            ['username', 'email', 'enabled', 'first_name', 'last_name', 'groups', 'roles'],
            ['new_user', 'new_user@example.com', '1', 'James', 'Smith', 'All', 'ROLE_USER'],
        ]);
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

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
