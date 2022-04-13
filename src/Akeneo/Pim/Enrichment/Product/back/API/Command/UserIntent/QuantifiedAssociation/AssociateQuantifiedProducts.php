<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AssociateQuantifiedProducts implements QuantifiedAssociationUserIntent
{
    /**
     * @param QuantifiedProduct[] $quantifiedProducts
     */
    public function __construct(private string $associationType, private array $quantifiedProducts)
    {
        Assert::stringNotEmpty($associationType);
        Assert::notEmpty($quantifiedProducts);
        Assert::allIsInstanceOf($quantifiedProducts, QuantifiedProduct::class);
    }

    public function associationType(): string
    {
        return $this->associationType;
    }

    /**
     * @return QuantifiedProduct[]
     */
    public function quantifiedProducts(): array
    {
        return $this->quantifiedProducts;
    }
}
