<?php
declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle\Import;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use Box\Spout\Writer\WriterFactory;

final class ImportUserGroupIntegration extends TestCase
{
    private const CSV_IMPORT_JOB_CODE = 'csv_user_group_import';
    private const XLSX_IMPORT_JOB_CODE = 'xlsx_user_group_import';

    protected JobLauncher $jobLauncher;
    protected GroupRepositoryInterface $groupRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->groupRepository = $this->get('pim_user.repository.group');

        $this->get(SqlCreateJobInstance::class)->createJobInstance([
            'code' => static::CSV_IMPORT_JOB_CODE,
            'label' => 'Test CSV',
            'job_name' => static::CSV_IMPORT_JOB_CODE,
            'status' => 0,
            'type' => 'import',
            'raw_parameters' => 'a:9:{s:8:"filePath";s:19:"/tmp/user_group.csv";s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:6:"escape";s:1:"\";s:10:"withHeader";b:1;s:13:"uploadAllowed";b:1;s:25:"invalid_items_file_format";s:3:"csv";s:14:"user_to_notify";N;s:21:"is_user_authenticated";b:0;}',
        ]);
        $this->get(SqlCreateJobInstance::class)->createJobInstance([
            'code' => static::XLSX_IMPORT_JOB_CODE,
            'label' => 'Test XLSX',
            'job_name' => static::XLSX_IMPORT_JOB_CODE,
            'status' => 0,
            'type' => 'import',
            'raw_parameters' => 'a:6:{s:8:"filePath";s:20:"/tmp/user_group.xlsx";s:10:"withHeader";b:1;s:13:"uploadAllowed";b:1;s:25:"invalid_items_file_format";s:4:"xlsx";s:14:"user_to_notify";N;s:21:"is_user_authenticated";b:0;}',
        ]);
    }

    /** @test */
    public function it_imports_user_groups_in_csv(): void
    {
        $csvContent = <<<CSV
name
All
"IT support"
Manager
Redactor
"New User Group CSV"

CSV;
        $this->jobLauncher->launchImport(static::CSV_IMPORT_JOB_CODE, $csvContent);
        $newGroup = $this->groupRepository->findOneByIdentifier('New User Group CSV');
        self::assertNotNull($newGroup);
        self::assertSame('New User Group CSV', $newGroup->getName());
    }

    /** @test */
    public function it_imports_user_groups_in_xlsx(): void
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'test_user_group_import');
        $writer = WriterFactory::create('xlsx');
        $writer->openToFile($temporaryFile);
        $writer->addRows([
            ['name'],
            ['All'],
            ['New User Group CSV XLSX'],
        ]);
        $writer->close();

        $this->jobLauncher->launchImport(static::XLSX_IMPORT_JOB_CODE, file_get_contents($temporaryFile), null, [], [], 'xlsx');
        $newGroup = $this->groupRepository->findOneByIdentifier('New User Group CSV XLSX');
        self::assertNotNull($newGroup);
        self::assertSame('New User Group CSV XLSX', $newGroup->getName());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
