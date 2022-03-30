<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplier;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject\Identifier;
use Symfony\Component\HttpFoundation\JsonResponse;

final class SupplierEdit
{
    public function __construct(private GetSupplier $getSupplier)
    {
    }

    public function __invoke(string $identifier): JsonResponse
    {
        $supplier = ($this->getSupplier)(Identifier::fromString($identifier));

        return new JsonResponse($supplier->toArray());
    }
}
