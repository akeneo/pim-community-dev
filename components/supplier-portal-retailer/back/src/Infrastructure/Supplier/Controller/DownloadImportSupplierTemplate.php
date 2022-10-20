<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Controller;

use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Encoder\SuppliersEncoder;
use OpenSpout\Common\Type;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

final class DownloadImportSupplierTemplate
{
    private const IMPORT_TEMPLATE_FILENAME = 'suppliers_import_template';

    public function __construct(private SuppliersEncoder $suppliersEncoder)
    {
    }

    public function __invoke(): BinaryFileResponse
    {
        $headers = [
            'Content-Disposition' => sprintf(
                'attachment; filename="%s.%s"',
                self::IMPORT_TEMPLATE_FILENAME,
                Type::XLSX,
            ),
        ];

        $filepath = ($this->suppliersEncoder)([]);

        return new BinaryFileResponse($filepath, Response::HTTP_OK, $headers);
    }
}
