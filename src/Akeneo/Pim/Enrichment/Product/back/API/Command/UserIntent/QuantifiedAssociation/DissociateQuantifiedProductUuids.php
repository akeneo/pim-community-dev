<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DissociateQuantifiedProductUuids implements QuantifiedAssociationUserIntent
{
    /**
     * @param UuidInterface[] $productUuids
     */
    public function __construct(private string $associationType, private array $productUuids)
    {
        Assert::stringNotEmpty($associationType);
        Assert::notEmpty($productUuids);
        Assert::allIsInstanceOf($productUuids, UuidInterface::class);
    }

    public function associationType(): string
    {
        return $this->associationType;
    }

    /**
     * @return UuidInterface[]
     */
    public function productUuids(): array
    {
        return $this->productUuids;
    }
}
