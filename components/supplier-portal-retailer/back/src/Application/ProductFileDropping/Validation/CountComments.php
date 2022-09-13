<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Validation;

use Symfony\Component\Validator\Constraint;

final class CountComments extends Constraint
{
    public string $message = 'supplier_portal.supplier.product_file_dropping.validation.comment.count_max_reached';
}
