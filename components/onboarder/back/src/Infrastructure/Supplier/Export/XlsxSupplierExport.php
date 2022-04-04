<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Export;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplierExport;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Export\SupplierExport;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Symfony\Component\Filesystem\Filesystem;

final class XlsxSupplierExport implements SupplierExport
{
    private GetSupplierExport $getSupplierExport;
    private array $headers;

    public function __construct(GetSupplierExport $getSupplierExport, array $headers)
    {
        $this->getSupplierExport = $getSupplierExport;
        $this->headers = $headers;
    }

    public function __invoke(): string
    {
        $filesystem = new Filesystem();
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'suppliers_export' . DIRECTORY_SEPARATOR .
            uniqid('', true);
        $filesystem->mkdir($directory);

        $writer = WriterFactory::createFromType(Type::XLSX);

        $filePath = tempnam($directory . DIRECTORY_SEPARATOR, 'suppliers_');
        $writer->openToFile($filePath);

        $writer->addRow(WriterEntityFactory::createRowFromArray($this->headers));

        foreach (($this->getSupplierExport)() as $supplier) {
            $writer->addRow(
                WriterEntityFactory::createRowFromArray(
                    [
                        $supplier->code,
                        $supplier->label,
                        implode(', ', $supplier->contributors)
                    ]
                )
            );
        }

        $writer->close();

        return $filePath;
    }
}
