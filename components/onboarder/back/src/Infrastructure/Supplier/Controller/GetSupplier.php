<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplier as GetSupplierQuery;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier\ValueObject\Identifier;
use Symfony\Component\HttpFoundation\JsonResponse;

final class GetSupplier
{
    public function __construct(private GetSupplierQuery $getSupplier)
    {
    }

    public function __invoke(string $identifier): JsonResponse
    {
        $supplier = ($this->getSupplier)(Identifier::fromString($identifier));

        return new JsonResponse($supplier->toArray());
    }
}
