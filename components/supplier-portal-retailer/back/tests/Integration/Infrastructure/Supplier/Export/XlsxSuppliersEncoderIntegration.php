<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Integration\Infrastructure\Supplier\Export;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\SupplierWithContributors;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Encoder\SuppliersEncoder;
use Akeneo\Tool\Component\Connector\Reader\File\SpoutReaderFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class XlsxSuppliersEncoderIntegration extends KernelTestCase
{
    /** @test */
    public function itExportsAXlsxFileContainingHeadersOnlyWhenThereIsNoSuppliers(): void
    {
        $filepath = $this->getContainer()->get(SuppliersEncoder::class)([]);

        $xlsxReader = SpoutReaderFactory::create(SpoutReaderFactory::XLSX);
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
        $suppliers = [
            new SupplierWithContributors(
                '44ce8069-8da1-4986-872f-311737f46f02',
                'supplier_a',
                'Supplier A',
                ['foo@foo.foo', 'bar@bar.bar'],
            ),
            new SupplierWithContributors(
                '0d321e66-7858-4db6-89bb-9c99283e5c58',
                'supplier_b',
                'Supplier B',
                [],
            ),
        ];

        $filepath = $this->getContainer()->get(SuppliersEncoder::class)($suppliers);

        $xlsxReader = SpoutReaderFactory::create(SpoutReaderFactory::XLSX);
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
}
