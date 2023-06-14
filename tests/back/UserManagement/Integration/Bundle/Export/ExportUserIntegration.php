<?php
declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle\Export;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\SqlCreateJobInstance;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Normalizer\DateTimeNormalizer;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

final class ExportUserIntegration extends TestCase
{
    private const CSV_EXPORT_JOB_CODE = 'csv_user_export';
    private const XLSX_EXPORT_JOB_CODE = 'xlsx_user_export';

    private JobLauncher $jobLauncher;
    private UserRepositoryInterface $userRepository;
    private DateTimeNormalizer $dateTimeNormalizer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->userRepository = $this->get('pim_user.repository.user');
        $this->dateTimeNormalizer = $this->get('pim_user.normalizer.date_time');

        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => self::CSV_EXPORT_JOB_CODE,
                'label' => 'Test CSV',
                'job_name' => self::CSV_EXPORT_JOB_CODE,
                'status' => 0,
                'type' => 'export',
                'raw_parameters' => 'a:0:{}',
            ]
        );
        $this->get(SqlCreateJobInstance::class)->createJobInstance(
            [
                'code' => self::XLSX_EXPORT_JOB_CODE,
                'label' => 'Test XLSX',
                'job_name' => self::XLSX_EXPORT_JOB_CODE,
                'status' => 0,
                'type' => 'export',
                'raw_parameters' => 'a:0:{}',
            ]
        );
    }

    /**
     * @test
     * @group ce
     */
    public function it_exports_users_in_csv(): void
    {
        /** @var UserInterface $admin */
        $admin = $this->userRepository->findOneBy(['username' => 'admin']);
        /** @var UserInterface $julia */
        $julia = $this->userRepository->findOneBy(['username' => 'julia']);
        /** @var UserInterface $mary */
        $mary = $this->userRepository->findOneBy(['username' => 'mary']);
        /** @var UserInterface $kevin */
        $kevin = $this->userRepository->findOneBy(['username' => 'kevin']);

        $expectedCsv = <<<CSV
        username;email;avatar;catalog_default_locale;catalog_default_scope;default_category_tree;default_product_grid_view;enabled;first_name;groups;last_name;middle_name;name_prefix;name_suffix;phone;product_grid_filters;roles;timezone;user_default_locale;date_account_created;date_account_last_updated;last_logged_in;login_count
        
        CSV;
        $expectedCsv .= "admin;admin@example.com;;en_US;ecommerce;master;;1;John;\"IT support\";Doe;;;;;;ROLE_ADMINISTRATOR;UTC;en_US;{$this->dateTimeNormalizer->normalize($admin->getCreatedAt())};{$this->dateTimeNormalizer->normalize($admin->getUpdatedAt())};;0\n";
        $expectedCsv .= "julia;julia@example.com;;en_US;ecommerce;master;;1;Julia;Manager;Stark;;;;;;ROLE_CATALOG_MANAGER;UTC;en_US;{$this->dateTimeNormalizer->normalize($julia->getCreatedAt())};{$this->dateTimeNormalizer->normalize($julia->getUpdatedAt())};;0\n";
        $expectedCsv .= "mary;mary@example.com;;en_US;ecommerce;master;;1;Mary;Redactor;Smith;;;;;;ROLE_USER;UTC;en_US;{$this->dateTimeNormalizer->normalize($mary->getCreatedAt())};{$this->dateTimeNormalizer->normalize($mary->getUpdatedAt())};;0\n";
        $expectedCsv .= "kevin;kevin@example.com;;en_US;ecommerce;master;;1;Kevin;Redactor;Michel;;;;;;ROLE_TRAINEE;UTC;en_US;{$this->dateTimeNormalizer->normalize($kevin->getCreatedAt())};{$this->dateTimeNormalizer->normalize($kevin->getUpdatedAt())};;0\n";
        $expectedCsv .= <<<CSV

        CSV;

        $csv = $this->jobLauncher->launchExport(self::CSV_EXPORT_JOB_CODE, null, []);

        self::assertSame($expectedCsv, $csv);
    }

    /** @test */
    public function it_exports_users_in_xlsx(): void
    {
        $xlsx = $this->jobLauncher->launchExport(self::XLSX_EXPORT_JOB_CODE, null, [], 'xlsx');
        self::assertNotEmpty($xlsx);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
