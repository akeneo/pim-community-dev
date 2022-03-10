<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Persistence\InMemory;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplierList;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier;

final class InMemoryGetSupplierList implements GetSupplierList
{
    private array $suppliers = [];

    public function save(Supplier\Model\Supplier $supplier): void
    {
        $this->suppliers[$supplier->identifier()] = $supplier;
    }

    public function __invoke(int $page = 1, string $search = ''): array
    {
        return '' === $search
            ?
            array_slice(
                $this->suppliers,
                self::NUMBER_OF_SUPPLIERS_PER_PAGE * ($page - 1),
                self::NUMBER_OF_SUPPLIERS_PER_PAGE
            )
            :
            array_slice(
                array_filter(
                    $this->suppliers,
                    fn (Supplier\Model\Supplier $supplier) => 1 <= strpos($supplier->label(), $search)
                ),
                self::NUMBER_OF_SUPPLIERS_PER_PAGE * ($page - 1),
                self::NUMBER_OF_SUPPLIERS_PER_PAGE
            )
        ;
    }
}
