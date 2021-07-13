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

namespace Akeneo\Platform\TailoredExport\Domain\SourceValue;

use Akeneo\Platform\TailoredExport\Domain\SourceValueInterface;
use Webmozart\Assert\Assert;

final class QuantifiedAssociationsValue implements SourceValueInterface
{
    /**
     * @return QuantifiedAssociation[]
     */
    private array $productAssociations;

    /**
     * @return QuantifiedAssociation[]
     */
    private array $productModelAssociations;

    public function __construct(
        array $productAssociations,
        array $productModelAssociations
    ) {
        Assert::allIsInstanceOf($productAssociations, QuantifiedAssociation::class);
        Assert::allIsInstanceOf($productModelAssociations, QuantifiedAssociation::class);

        $this->productAssociations = $productAssociations;
        $this->productModelAssociations = $productModelAssociations;
    }

    /**
     * @return string[]
     */
    public function getAssociatedProductIdentifiers(): array
    {
        return array_map(static fn (QuantifiedAssociation $productAssociation) => $productAssociation->getIdentifier(),$this->productAssociations);
    }

    /**
     * @return string[]
     */
    public function getAssociatedProductModelCodes(): array
    {
        return array_map(static fn (QuantifiedAssociation $productModelAssociation) => $productModelAssociation->getIdentifier(),$this->productModelAssociations);
    }

    /**
     * @return int[]
     */
    public function getAssociatedProductQuantities(): array
    {
        return array_map(static fn (QuantifiedAssociation $productAssociation) => $productAssociation->getQuantity(),$this->productAssociations);
    }

    /**
     * @return int[]
     */
    public function getAssociatedProductModelQuantities(): array
    {
        return array_map(static fn (QuantifiedAssociation $productModelAssociation) => $productModelAssociation->getQuantity(),$this->productModelAssociations);
    }
}
