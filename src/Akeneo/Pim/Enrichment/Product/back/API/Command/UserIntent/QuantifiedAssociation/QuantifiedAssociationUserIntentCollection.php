<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QuantifiedAssociationUserIntentCollection implements UserIntent
{
    /**
     * @param array<QuantifiedAssociationUserIntent> $quantifiedAssociationUserIntents
     */
    public function __construct(
        private array $quantifiedAssociationUserIntents = []
    ) {
        Assert::allIsInstanceOf($this->quantifiedAssociationUserIntents, QuantifiedAssociationUserIntent::class);
    }

    /**
     * @return array<QuantifiedAssociationUserIntent>
     */
    public function quantifiedAssociationUserIntents(): array
    {
        return $this->quantifiedAssociationUserIntents;
    }
}
