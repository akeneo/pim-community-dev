<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileImport\ServiceApi;

use Akeneo\Platform\Job\ServiceApi\JobInstance\FindJobInstanceInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\JobInstanceQuery;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\ServiceApi\FindAllTailoredImportProfiles;
use Akeneo\Platform\Job\ServiceApi\JobInstance\JobInstance;
use PHPUnit\Framework\TestCase;

final class FindAllTailoredImportProfilesTest extends TestCase
{
    /** @test */
    public function itReturnsAnArrayOfProductFileImports(): void
    {
        $findJobInstances = $this->createMock(FindJobInstanceInterface::class);
        $findJobInstances
            ->expects($this->once())
            ->method('fromQuery')
            ->with(new JobInstanceQuery(['xlsx_tailored_product_import']))
            ->willReturn([
                new JobInstance('import1', 'Import 1'),
                new JobInstance('import2', 'Import 2'),
            ]);

        $sut = new FindAllTailoredImportProfiles($findJobInstances);

        $this->assertEquals([
            new ProductFileImport('import1', 'Import 1'),
            new ProductFileImport('import2', 'Import 2'),
        ], ($sut)());
    }
}
