<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type AutoNumberNormalized from AutoNumber
 * @phpstan-import-type FreeTextNormalized from FreeText
 * @phpstan-type PropertyNormalized AutoNumberNormalized | FreeTextNormalized
 */
interface PropertyInterface
{
    /**
     * @return PropertyNormalized
     */
    public function normalize(): array;

    /**
     * @param array<string, mixed> $fromNormalized
     * @return self
     */
    public static function fromNormalized(array $fromNormalized): self;

    public static function type(): string;

    public function match(ProductProjection $productProjection): bool;
}
