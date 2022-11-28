<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Domain;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ProductSelectionCriterion array{
 *      field: string,
 *      operator: string,
 *      value?: mixed,
 *      scope?: string,
 *      locale?: string,
 * }
 */
final class ProductSelectionCriteria
{
    /**
     * @param array<array-key, ProductSelectionCriterion> $productSelectionCriteria
     */
    public function __construct(private array $productSelectionCriteria = [])
    {
    }

    /**
     * @return array<ProductSelectionCriterion>
     */
    public function toArray(): array
    {
        return $this->productSelectionCriteria;
    }
}
