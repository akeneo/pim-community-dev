<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model;

class ProductFileNameAndResourceFile
{
    //@phpstan-ignore-next-line
    public function __construct(public string $originalFilename, public $file)
    {
    }
}
