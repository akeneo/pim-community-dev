<?php

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model\SupplierFile;

interface GetProductFiles
{
    /**
     * @return SupplierFile[]
     */
    public function __invoke(Email $contributorEmail): array;
}
