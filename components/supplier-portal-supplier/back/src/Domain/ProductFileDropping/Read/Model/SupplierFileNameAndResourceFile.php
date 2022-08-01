<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model;

final class SupplierFileNameAndResourceFile
{
    public function __construct(public string $originalFilename, public $file)
    {
    }
}
