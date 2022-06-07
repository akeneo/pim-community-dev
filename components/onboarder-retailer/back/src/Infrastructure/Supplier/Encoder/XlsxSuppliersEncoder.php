<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Encoder;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

final class XlsxSuppliersEncoder implements SuppliersEncoder
{
    private const HEADERS = ['supplier_code', 'supplier_label', 'contributor_emails'];

    public function __construct(private LoggerInterface $onboarderSerenityLogger)
    {
    }

    public function __invoke(array $suppliersWithContributors): string
    {
        $filesystem = new Filesystem();
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'suppliers_export' . DIRECTORY_SEPARATOR .
            uniqid('', true);
        $filesystem->mkdir($directory);

        $writer = WriterFactory::createFromType(Type::XLSX);

        $filePath = tempnam($directory . DIRECTORY_SEPARATOR, 'suppliers_');
        try {
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
        } catch (IOException|WriterNotOpenedException $e) {
            $this->onboarderSerenityLogger->error(
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
