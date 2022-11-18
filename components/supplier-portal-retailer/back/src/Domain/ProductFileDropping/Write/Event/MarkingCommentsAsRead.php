<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event;

final class MarkingCommentsAsRead
{
    public function __construct(public readonly \DateTimeImmutable $date, public readonly string $productFileIdentifier)
    {
    }
}
