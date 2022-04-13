<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QuantifiedAssociationUserIntentCollectionApplier implements UserIntentApplier
{
    private const PRODUCTS = 'products';
    private const PRODUCT_MODELS = 'product_models';

    public function __construct(
        private ObjectUpdaterInterface $productUpdater,
        private GetViewableProducts $getViewableProducts,
        private GetViewableProductModels $getViewableProductModels
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedUserIntents(): array
    {
        return [QuantifiedAssociationUserIntentCollection::class];
    }

    /**
     * {@inheritDoc}
     */
    public function apply(UserIntent $userIntent, ProductInterface $product, int $userId): void
    {
        Assert::isInstanceOf($userIntent, QuantifiedAssociationUserIntentCollection::class);

        $normalizedQuantifiedAssociations = [];

        /** @var QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent */
        foreach ($userIntent->quantifiedAssociationUserIntents() as $quantifiedAssociationUserIntent) {
            $associationType = $quantifiedAssociationUserIntent->associationType();
            $entityType = $this->getAssociationEntityType($quantifiedAssociationUserIntent);

            $formerAssociations = $normalizedQuantifiedAssociations[$associationType][$entityType]
                ?? $this->getFormerAssociations($quantifiedAssociationUserIntent, $product, $entityType);

            $values = match ($quantifiedAssociationUserIntent::class) {
                AssociateQuantifiedProducts::class, AssociateQuantifiedProductModels::class =>
                    $this->associateQuantifiedEntities(
                        $formerAssociations,
                        $quantifiedAssociationUserIntent
                    ),
                DissociateQuantifiedProducts::class, DissociateQuantifiedProductModels::class =>
                    $this->dissociateQuantifiedEntities(
                        $formerAssociations,
                        $quantifiedAssociationUserIntent
                    ),
                ReplaceAssociatedQuantifiedProducts::class => $this->replaceQuantifiedEntities(
                    $formerAssociations,
                    $quantifiedAssociationUserIntent,
                    $userId
                ),
                default => throw new \InvalidArgumentException('Unsupported association userIntent')
            };
            if (\is_null($values)) {
                continue;
            }
            $normalizedQuantifiedAssociations[$quantifiedAssociationUserIntent->associationType()][$entityType] = $values;
        }

        if ([] === $normalizedQuantifiedAssociations) {
            return;
        }

        $this->productUpdater->update($product, ['quantified_associations' => $normalizedQuantifiedAssociations]);
    }

    /**
     * @return array<array{identifier: string, quantity: int}>
     */
    private function getFormerAssociations(
        QuantifiedAssociationUserIntent $associationUserIntent,
        ProductInterface $product,
        string $entityType
    ): array {
        $normalizedQuantifiedAssociations = $product->getQuantifiedAssociations()->normalize();

        return $normalizedQuantifiedAssociations[$associationUserIntent->associationType()][$entityType] ?? [];
    }

    private function getAssociationEntityType(QuantifiedAssociationUserIntent $userIntent): string
    {
        return match ($userIntent::class) {
            AssociateQuantifiedProducts::class, DissociateQuantifiedProducts::class, ReplaceAssociatedQuantifiedProducts::class =>
                self::PRODUCTS,
            AssociateQuantifiedProductModels::class, DissociateQuantifiedProductModels::class => self::PRODUCT_MODELS,
            default => throw new \LogicException('User intent cannot be handled')
        };
    }

    /**
     * @param array<array{identifier: string, quantity: int}> $formerAssociations
     * @return array<array{identifier: string, quantity: int}>|null
     */
    private function associateQuantifiedEntities(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent
    ): ?array {
        if ($quantifiedAssociationUserIntent instanceof AssociateQuantifiedProducts) {
            $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProducts();
        } elseif ($quantifiedAssociationUserIntent instanceof AssociateQuantifiedProductModels) {
            $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProductModels();
        } else {
            throw new \InvalidArgumentException('Unexpected user intent');
        }
        $indexedFormerAssociations = [];
        foreach ($formerAssociations as $formerAssociation) {
            $indexedFormerAssociations[$formerAssociation['identifier']] = $formerAssociation;
        }

        $isUpdated = false;
        foreach ($quantifiedEntities as $quantifiedEntity) {
            $identifier = $quantifiedEntity->entityIdentifier();
            if (\array_key_exists($identifier, $indexedFormerAssociations)
                && $indexedFormerAssociations[$identifier]['quantity'] === $quantifiedEntity->quantity()
            ) {
                continue;
            }

            $indexedFormerAssociations[$identifier] = [
                'identifier' => $identifier,
                'quantity' => $quantifiedEntity->quantity(),
            ];
            $isUpdated = true;
        }

        return $isUpdated ? \array_values($indexedFormerAssociations) : null;
    }

    /**
     * @param array<array{identifier: string, quantity: int}> $formerAssociations
     * @return array<array{identifier: string, quantity: int}>|null
     */
    private function dissociateQuantifiedEntities(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent
    ): ?array {
        if ($quantifiedAssociationUserIntent instanceof DissociateQuantifiedProducts) {
            $entityIdentifiers = $quantifiedAssociationUserIntent->productIdentifiers();
        } elseif ($quantifiedAssociationUserIntent instanceof DissociateQuantifiedProductModels) {
            $entityIdentifiers = $quantifiedAssociationUserIntent->productModelCodes();
        } else {
            throw new \InvalidArgumentException('Unexpected user intent');
        }
        $newAssociations = \array_filter(
            $formerAssociations,
            static fn (array $association): bool => !\in_array($association['identifier'], $entityIdentifiers)
        );

        return \count($newAssociations) === \count($formerAssociations) ? null : \array_values($newAssociations);
    }

    /**
     * @param array<array{identifier: string, quantity: int}> $formerAssociations
     * @return array<array{identifier: string, quantity: int}>|null
     */
    private function replaceQuantifiedEntities(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent,
        int $userId
    ): ?array {
        Assert::methodExists($quantifiedAssociationUserIntent, 'quantifiedProducts');

        $newAssociations = [];
        /** @var QuantifiedEntity $quantifiedProduct */
        foreach ($quantifiedAssociationUserIntent->quantifiedProducts() as $quantifiedProduct) {
            $newAssociations[] = [
                'identifier' => $quantifiedProduct->entityIdentifier(),
                'quantity' => $quantifiedProduct->quantity(),
            ];
        }

        $sortFunction = fn (array $a, array $b): int => \strcmp($a['identifier'], $b['identifier']);
        \usort($newAssociations, $sortFunction);
        \usort($formerAssociations, $sortFunction);
        if ($newAssociations === $formerAssociations) {
            return null;
        }

        $formerAssociatedIdentifiers = \array_column($formerAssociations, 'identifier');
        $viewableIdentifiers = $this->getViewableProducts->fromProductIdentifiers($formerAssociatedIdentifiers, $userId);
        $nonViewableFormerAssociations = \array_values(\array_filter(
            $formerAssociations,
            static fn (array $association): bool => !\in_array($association['identifier'], $viewableIdentifiers)
        ));

        return \array_values(\array_merge($newAssociations, $nonViewableFormerAssociations));
    }
}
