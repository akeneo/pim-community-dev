<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Webmozart\Assert\Assert;

/**
 * For the given association type, the former associated product models that are not defined in this object
 * will be dissociated.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReplaceAssociatedQuantifiedProductModels implements QuantifiedAssociationUserIntent
{
    /**
     * @param QuantifiedEntity[] $quantifiedProductModels
     */
    public function __construct(private string $associationType, private array $quantifiedProductModels)
    {
        Assert::stringNotEmpty($associationType);
        Assert::allIsInstanceOf($quantifiedProductModels, QuantifiedEntity::class);
    }

    public function associationType(): string
    {
        return $this->associationType;
    }

    /**
     * @return QuantifiedEntity[]
     */
    public function quantifiedProductModels(): array
    {
        return $this->quantifiedProductModels;
    }
}
