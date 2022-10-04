<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\Supplier\Write\Validation;

use Symfony\Component\Validator\Constraint;

final class UniqueContributorEmail extends Constraint
{
    public string $message = 'supplier_portal.supplier.contributor.email_already_exists';
}
