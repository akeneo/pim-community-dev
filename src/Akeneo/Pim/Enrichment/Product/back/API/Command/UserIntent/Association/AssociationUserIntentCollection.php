<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Webmozart\Assert\Assert;

/**
 * Association user intent collection can either contain identifier related user intents or uuid related user intents
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AssociationUserIntentCollection implements UserIntent
{
    private array $associationUserIntentsByUuids = [
        AssociateProductUuids::class,
        DissociateProductUuids::class,
        ReplaceAssociatedProductUuids::class,
        AssociateQuantifiedProductUuids::class,
        DissociateQuantifiedProductUuids::class,
        ReplaceAssociatedQuantifiedProductUuids::class
    ];

    /**
     * @param array<AssociationUserIntent> $associationUserIntents
     */
    public function __construct(private array $associationUserIntents = []) {
        Assert::allIsInstanceOf($this->associationUserIntents, AssociationUserIntent::class);

        $associationUserIntentsIndexedByAssociationType = [];
        foreach ($this->associationUserIntents as $userIntent) {
            $associationUserIntentsIndexedByAssociationType[$userIntent->associationType()][] = $userIntent::class;
        }

        foreach ($associationUserIntentsIndexedByAssociationType as $type => $userIntents) {
            $diffCount = count(\array_diff($userIntents, $this->associationUserIntentsByUuids));
            $firstIsWithUuid = \in_array($userIntents[0], $this->associationUserIntentsByUuids);
            $isAllUuids = $firstIsWithUuid && $diffCount === 0;
            $isAllIdentifiers = !$firstIsWithUuid && $diffCount === count($this->associationUserIntentsByUuids);
            if (!$isAllUuids || !$isAllIdentifiers) {
                throw new \InvalidArgumentException('AssociationUserIntentCollection should contain either only uuids user intents or identifier user intents');
            }
        }
    }

    /**
     * @return array<AssociationUserIntent>
     */
    public function associationUserIntents(): array
    {
        return $this->associationUserIntents;
    }
}
