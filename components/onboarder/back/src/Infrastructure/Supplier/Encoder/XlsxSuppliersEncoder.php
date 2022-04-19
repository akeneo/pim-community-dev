<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Encoder;

use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Symfony\Component\Filesystem\Filesystem;

final class XlsxSuppliersEncoder implements SuppliersEncoder
{
    private const HEADERS = ['supplier_code', 'supplier_label', 'contributor_emails'];

    public function __invoke(array $suppliersWithContributors): string
    {
        $filesystem = new Filesystem();
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'suppliers_export' . DIRECTORY_SEPARATOR .
            uniqid('', true);
        $filesystem->mkdir($directory);

        $writer = WriterFactory::createFromType(Type::XLSX);

        $filePath = tempnam($directory . DIRECTORY_SEPARATOR, 'suppliers_');
        $writer->openToFile($filePath);

        $writer->addRow(WriterEntityFactory::createRowFromArray(self::HEADERS));

        foreach ($suppliersWithContributors as $supplierWithContributors) {
            $writer->addRow(
                WriterEntityFactory::createRowFromArray(
                    [
                        $supplierWithContributors->code,
                        $supplierWithContributors->label,
                        implode(', ', $supplierWithContributors->contributors),
                    ],
                ),
            );
        }

        $writer->close();

        return $filePath;
    }
}
