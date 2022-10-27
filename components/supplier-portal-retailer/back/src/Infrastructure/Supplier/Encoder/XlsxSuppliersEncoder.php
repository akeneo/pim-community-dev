<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Encoder;

use Akeneo\Tool\Component\Connector\Writer\File\SpoutWriterFactory;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

final class XlsxSuppliersEncoder implements SuppliersEncoder
{
    private const HEADERS = ['supplier_code', 'supplier_label', 'contributor_emails'];

    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(array $suppliersWithContributors): string
    {
        $filesystem = new Filesystem();
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'suppliers_export' . DIRECTORY_SEPARATOR .
            uniqid('', true);
        $filesystem->mkdir($directory);

        $writer = SpoutWriterFactory::create(SpoutWriterFactory::XLSX);

        $filePath = tempnam($directory . DIRECTORY_SEPARATOR, 'suppliers_');
        try {
            $writer->openToFile($filePath);
            $writer->addRow(Row::fromValues(self::HEADERS));

            foreach ($suppliersWithContributors as $supplierWithContributors) {
                $writer->addRow(
                    Row::fromValues(
                        [
                            $supplierWithContributors->code,
                            $supplierWithContributors->label,
                            implode(', ', $supplierWithContributors->contributors),
                        ],
                    ),
                );
            }
        } catch (IOException|WriterNotOpenedException $e) {
            $this->logger->error(
                sprintf(
                    'An error occurred while encoding suppliers: "%s"',
                    $e->getMessage(),
                ),
                [
                    'data' => [
                        'filepath' => $filePath,
                        'suppliers' => $suppliersWithContributors,
                    ],
                ],
            );
        }

        $writer->close();

        return $filePath;
    }
}
