<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\ServiceApi;

use Akeneo\Platform\Job\ServiceApi\JobInstance\FindJobInstanceInterface;
use Akeneo\Platform\Job\ServiceApi\JobInstance\JobInstance;
use Akeneo\Platform\Job\ServiceApi\JobInstance\JobInstanceQuery;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\FindAllProductFileImports;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model\ProductFileImport;

final class FindAllTailoredImportProfiles implements FindAllProductFileImports
{
    public function __construct(private FindJobInstanceInterface $findJobInstance)
    {
    }

    public function __invoke(): array
    {
        $jobInstances = $this->findJobInstance->fromQuery(new JobInstanceQuery(['xlsx_tailored_product_import']));

        return array_map(
            fn (JobInstance $jobInstance) => new ProductFileImport($jobInstance->getCode(), $jobInstance->getLabel()),
            $jobInstances,
        );
    }
}
