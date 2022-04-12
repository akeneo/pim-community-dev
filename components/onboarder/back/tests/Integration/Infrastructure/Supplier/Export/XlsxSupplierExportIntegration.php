<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Integration\Infrastructure\Supplier\Export;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Export\SupplierExport;
use Akeneo\OnboarderSerenity\Test\Integration\SqlIntegrationTestCase;
use Box\Spout\Common\Type;
use Box\Spout\Reader\Common\Creator\ReaderFactory;
use Doctrine\DBAL\Connection;

final class XlsxSupplierExportIntegration extends SqlIntegrationTestCase
{
    /** @test */
    public function itExportsAXlsxFileContainingHeadersOnlyWhenThereIsNoSuppliers(): void
    {
        $filepath = $this->get(SupplierExport::class)();

        $xlsxReader = ReaderFactory::createFromType(Type::XLSX);
        $xlsxReader->open($filepath);
        $sheet = current(iterator_to_array($xlsxReader->getSheetIterator()));

        $rows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $rows[] = $row->toArray();
        }

        $xlsxReader->close();

        static::assertFileExists($filepath);
        static::assertSame([['supplier_code', 'supplier_label', 'contributor_emails']], $rows);
    }

    /** @test */
    public function itExportsAXlsxFileContainingAllTheSuppliers(): void
    {
        $this->createSupplier(
            '44ce8069-8da1-4986-872f-311737f46f02',
            'supplier_a',
            'Supplier A',
        );
        $this->createSupplier(
            '0d321e66-7858-4db6-89bb-9c99283e5c58',
            'supplier_b',
            'Supplier B',
        );

        $this->createContributor('foo@foo.foo', '44ce8069-8da1-4986-872f-311737f46f02');
        $this->createContributor('bar@bar.bar', '44ce8069-8da1-4986-872f-311737f46f02');

        $filepath = $this->get(SupplierExport::class)();

        $xlsxReader = ReaderFactory::createFromType(Type::XLSX);
        $xlsxReader->open($filepath);
        $sheet = current(iterator_to_array($xlsxReader->getSheetIterator()));

        $rows = [];
        foreach ($sheet->getRowIterator() as $row) {
            $rows[] = $row->toArray();
        }

        $xlsxReader->close();

        static::assertFileExists($filepath);
        static::assertSame(
            [
                ['supplier_code', 'supplier_label', 'contributor_emails'],
                ['supplier_a', 'Supplier A', 'foo@foo.foo, bar@bar.bar'],
                ['supplier_b', 'Supplier B', ''],
            ],
            $rows,
        );
    }

    private function createSupplier(string $identifier, string $code, string $label): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_onboarder_serenity_supplier` (identifier, code, label)
            VALUES (:identifier, :code, :label)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'identifier' => $identifier,
                'code' => $code,
                'label' => $label,
            ],
        );
    }

    private function createContributor(string $email, string $supplierIdentifier): void
    {
        $sql = <<<SQL
            INSERT INTO `akeneo_onboarder_serenity_supplier_contributor` (email, supplier_identifier)
            VALUES (:email, :supplierIdentifier)
        SQL;

        $this->get(Connection::class)->executeQuery(
            $sql,
            [
                'email' => $email,
                'supplierIdentifier' => $supplierIdentifier,
            ],
        );
    }
}
