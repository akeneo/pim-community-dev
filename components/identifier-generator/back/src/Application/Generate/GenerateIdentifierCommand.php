<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GenerateIdentifierCommand
{
    private function __construct(
        private IdentifierGenerator $identifierGenerator,
        private ProductProjection $productProjection,
    ) {
    }

    public static function fromIdentifierGenerator(
        IdentifierGenerator $identifierGenerator,
        ProductProjection $productProjection
    ): self {
        return new self($identifierGenerator, $productProjection);
    }

    public function getIdentifierGenerator(): IdentifierGenerator
    {
        return $this->identifierGenerator;
    }

    public function getProductProjection(): ProductProjection
    {
        return $this->productProjection;
    }
}
