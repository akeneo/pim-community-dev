<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Match;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;

final class MatchIdentifierGeneratorQuery
{
    public function __construct(
        private readonly IdentifierGenerator $identifierGenerator,
        private readonly ProductProjection $productProjection
    ) {
    }

    public function identifierGenerator(): IdentifierGenerator
    {
        return $this->identifierGenerator;
    }

    public function productProjection(): ProductProjection
    {
        return $this->productProjection;
    }
}
