<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

interface ProductEntityIdCollection extends \IteratorAggregate, \Countable
{
    /**  @param array<string> $productEntityIds */
    public static function fromStrings(array $productEntityIds): self;

    /** @return array<ProductEntityIdInterface> */
    public function toArray(): array;

    /**  @return array<string> */
    public function toArrayString(): array;

    public function isEmpty(): bool;
}
