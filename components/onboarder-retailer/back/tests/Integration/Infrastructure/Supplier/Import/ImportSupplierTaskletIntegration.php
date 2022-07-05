<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Integration\Infrastructure\Supplier\Import;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\OnboarderSerenity\Retailer\Test\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class ImportSupplierTaskletIntegration extends SqlIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        parent::bootKernel();
    }

    /** @test */
    public function itImportsSuppliersFromAXlsxFile(): void
    {
        $filePath = sprintf(
            '%s/components/onboarder-retailer/back/tests/Integration/files/suppliers_import.xlsx',
            static::$kernel->getProjectDir(),
        );

        $this->runOnboarderSerenityXlsxSupplierImportJob($filePath);
        $supplier = $this->findSupplierByCode('supplier_1');

        static::assertNotNull($supplier);
        static::assertSame('Supplier 1', $supplier->label());
        static::assertCount(2, $supplier->contributors());
    }

    /** @test */
    public function itImportsNothingIfThereIsNoSuppliersInTheXlsxFile(): void
    {
        $filePath = sprintf(
            '%s/components/onboarder-retailer/back/tests/Integration/files/suppliers_import_empty.xlsx',
            static::$kernel->getProjectDir(),
        );

        $this->runOnboarderSerenityXlsxSupplierImportJob($filePath);

        static::assertSame(0, $this->countSuppliers());
    }

    private function runOnboarderSerenityXlsxSupplierImportJob(string $filePath): void
    {
        $jobInstanceRepository = $this->get('pim_enrich.repository.job_instance');
        $jobInstance = $jobInstanceRepository->findOneBy(['code' => 'onboarder_serenity_xlsx_supplier_import']);

        $jobParameters = new JobParameters(['filePath' => $filePath]);
        $jobExecution = new JobExecution();
        $jobExecution->setJobParameters($jobParameters);
        $jobExecution->setJobInstance($jobInstance);

        $em = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');

        $em->persist($jobExecution);
        $em->flush();

        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'akeneo:batch:job',
            'code' => 'onboarder_serenity_xlsx_supplier_import',
            'execution' => $jobExecution->getId(),
        ]);

        $output = new BufferedOutput();
        $application->run($input, $output);
    }

    private function findSupplierByCode(string $code): ?Supplier
    {
        $sql = <<<SQL
            WITH contributor AS (
                SELECT supplier_identifier, JSON_ARRAYAGG(email) as contributor_emails
                FROM `akeneo_supplier_portal_supplier_contributor` contributor
                GROUP BY contributor.supplier_identifier
            )
            SELECT identifier, code, label, contributor.contributor_emails
            FROM `akeneo_supplier_portal_supplier` supplier
            LEFT JOIN contributor ON contributor.supplier_identifier = supplier.identifier
            WHERE code = :code
        SQL;

        $row = static::$kernel->getContainer()->get('database_connection')->executeQuery(
            $sql,
            [
                'code' => $code,
            ],
        )->fetchAssociative();

        return false !== $row ? Supplier::create(
            $row['identifier'],
            $row['code'],
            $row['label'],
            null !== $row['contributor_emails']
                ? json_decode($row['contributor_emails'], true)
                : [],
        ) : null;
    }

    private function countSuppliers(): int
    {
        $sql = <<<SQL
            SELECT COUNT(*)
            FROM `akeneo_supplier_portal_supplier`
        SQL;

        return (int) static::$kernel->getContainer()->get('database_connection')->executeQuery($sql)->fetchOne();
    }
}
