<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\QuantifiedAssociations;

use Webmozart\Assert\Assert;

final class QuantifiedAssociationsQuantitySelection implements QuantifiedAssociationsSelectionInterface
{
    public const TYPE = 'quantity';

    private string $entityType;
    private string $separator;

    public function __construct(string $entityType, string $separator)
    {
        Assert::inArray($entityType, [
            self::ENTITY_TYPE_PRODUCTS,
            self::ENTITY_TYPE_PRODUCT_MODELS,
        ]);

        $this->entityType = $entityType;
        $this->separator = $separator;
    }

    public function isProductsSelection(): bool
    {
        return self::ENTITY_TYPE_PRODUCTS === $this->entityType;
    }

    public function isProductModelsSelection(): bool
    {
        return self::ENTITY_TYPE_PRODUCT_MODELS === $this->entityType;
    }

    public function getSeparator(): string
    {
        return $this->separator;
    }

    public function getAllLocaleCodes(): array
    {
        return [];
    }

    public function getAllAttributeCodes(): array
    {
        return [];
    }
}
