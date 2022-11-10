<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileImport\Write\Model\ProductFileImport;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport\ImportExecutionId;
use PHPUnit\Framework\TestCase;

final class ImportExecutionIdTest extends TestCase
{
    /** @test */
    public function itCreatesAnImportExecutionId(): void
    {
        $importExecutionId = ImportExecutionId::fromInt(666);
        $this->assertSame(666, $importExecutionId->getId());
    }
}
