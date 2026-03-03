<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Webmozart\Assert\Assert;

/**
 * For the given association type, the former associated products that are not defined in this object
 * will be dissociated.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReplaceAssociatedQuantifiedProductUuids implements QuantifiedAssociationUserIntent
{
    /**
     * @param QuantifiedEntity[] $quantifiedProducts
     */
    public function __construct(private string $associationType, private array $quantifiedProducts)
    {
        Assert::stringNotEmpty($associationType);
        Assert::allIsInstanceOf($quantifiedProducts, QuantifiedEntity::class);
    }

    public function associationType(): string
    {
        return $this->associationType;
    }

    /**
     * @return QuantifiedEntity[]
     */
    public function quantifiedProducts(): array
    {
        return $this->quantifiedProducts;
    }
}
