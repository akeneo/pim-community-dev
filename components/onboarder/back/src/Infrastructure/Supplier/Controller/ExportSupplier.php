<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

final class ExportSupplier
{
    private array $headers;

    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    public function __invoke(): BinaryFileResponse
    {
        $filesystem = new Filesystem();
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'suppliers_export' . DIRECTORY_SEPARATOR .
            uniqid('', true);
        $filesystem->mkdir($directory);

        $writer = WriterFactory::createFromType(Type::XLSX);

        $filePath = tempnam($directory . DIRECTORY_SEPARATOR, 'suppliers_');
        $writer->openToFile($filePath);

        $writer->addRow(WriterEntityFactory::createRowFromArray($this->headers));

        $writer->close();

        $headers = [
            'Content-Disposition' => sprintf(
                'attachment; filename="%s.%s"',
                'suppliers_export',
                Type::XLSX
            ),
        ];

        return new BinaryFileResponse($filePath, Response::HTTP_OK, $headers);
    }
}
