<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetAllSuppliersWithContributors;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Encoder\SuppliersEncoder;
use Box\Spout\Common\Type;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

final class ExportSupplier
{
    private const EXPORT_FILENAME = 'suppliers_export';

    public function __construct(
        private GetAllSuppliersWithContributors $getAllSuppliersWithContributors,
        private SuppliersEncoder $suppliersEncoder,
    ) {
    }

    public function __invoke(): BinaryFileResponse
    {
        $headers = [
            'Content-Disposition' => sprintf(
                'attachment; filename="%s.%s"',
                self::EXPORT_FILENAME,
                Type::XLSX,
            ),
        ];

        $suppliersWithContributors = ($this->getAllSuppliersWithContributors)();
        $filepath = ($this->suppliersEncoder)($suppliersWithContributors);

        return new BinaryFileResponse($filepath, Response::HTTP_OK, $headers);
    }
}
