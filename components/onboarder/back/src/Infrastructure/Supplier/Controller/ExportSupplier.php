<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Export\SupplierExport;
use Box\Spout\Common\Type;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

final class ExportSupplier
{
    private const EXPORT_FILENAME = 'suppliers_export';

    public function __construct(private SupplierExport $supplierExport)
    {
    }

    public function __invoke(): BinaryFileResponse
    {
        $headers = [
            'Content-Disposition' => sprintf(
                'attachment; filename="%s.%s"',
                self::EXPORT_FILENAME,
                Type::XLSX
            ),
        ];

        return new BinaryFileResponse(($this->supplierExport)(), Response::HTTP_OK, $headers);
    }
}
