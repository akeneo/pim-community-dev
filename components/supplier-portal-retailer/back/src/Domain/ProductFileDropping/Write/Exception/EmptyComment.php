<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception;

final class EmptyComment extends \InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('supplier_portal.product_file_dropping.supplier_files.discussion.empty_content');
    }
}
