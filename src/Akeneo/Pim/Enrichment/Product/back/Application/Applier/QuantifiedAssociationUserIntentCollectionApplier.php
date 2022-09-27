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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Ramsey\Uuid\Uuid;
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
                AssociateQuantifiedProducts::class =>
                    $this->associateQuantifiedProducts(
                        $formerAssociations,
                        $quantifiedAssociationUserIntent
                    ),
                AssociateQuantifiedProductModels::class =>
                    $this->associateQuantifiedProductModels(
                        $formerAssociations,
                        $quantifiedAssociationUserIntent
                    ),
                DissociateQuantifiedProducts::class =>
                    $this->dissociateQuantifiedProducts(
                        $formerAssociations,
                        $quantifiedAssociationUserIntent
                    ),
                DissociateQuantifiedProductModels::class =>
                    $this->dissociateQuantifiedProductModels(
                        $formerAssociations,
                        $quantifiedAssociationUserIntent
                    ),
                ReplaceAssociatedQuantifiedProducts::class =>
                    $this->replaceQuantifiedProducts(
                        $formerAssociations,
                        $quantifiedAssociationUserIntent,
                        $userId
                    ),
                ReplaceAssociatedQuantifiedProductModels::class =>
                    $this->replaceQuantifiedProductModels(
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
            AssociateQuantifiedProducts::class, DissociateQuantifiedProducts::class, ReplaceAssociatedQuantifiedProducts::class
                => self::PRODUCTS,
            AssociateQuantifiedProductModels::class, DissociateQuantifiedProductModels::class, ReplaceAssociatedQuantifiedProductModels::class
                => self::PRODUCT_MODELS,
            default => throw new \LogicException('User intent cannot be handled')
        };
    }

    /**
     * @param array<array{identifier: string, quantity: int}> $formerAssociations
     * @return array<array{identifier: string, quantity: int}>|null
     */
    private function associateQuantifiedProducts(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent
    ): ?array {
        if (!$quantifiedAssociationUserIntent instanceof AssociateQuantifiedProducts) {
            throw new \InvalidArgumentException('Unexpected user intent');
        }

        $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProducts();
        $indexedFormerAssociations = [];
        foreach ($formerAssociations as $formerAssociation) {
            $indexedFormerAssociations[$formerAssociation['uuid']] = $formerAssociation;
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
                'uuid' => $identifier,
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
    private function associateQuantifiedProductModels(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent
    ): ?array {
        if (!$quantifiedAssociationUserIntent instanceof AssociateQuantifiedProductModels) {
            throw new \InvalidArgumentException('Unexpected user intent');
        }

        $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProductModels();
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
    private function dissociateQuantifiedProducts(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent
    ): ?array {
        if (!$quantifiedAssociationUserIntent instanceof DissociateQuantifiedProducts) {
            throw new \InvalidArgumentException('Unexpected user intent');
        }

        $entityIdentifiers = $quantifiedAssociationUserIntent->productIdentifiers();
        $newAssociations = \array_filter(
            $formerAssociations,
            static fn (array $association): bool => !\in_array($association['uuid'], $entityIdentifiers)
        );

        return \count($newAssociations) === \count($formerAssociations) ? null : \array_values($newAssociations);
    }

    /**
     * @param array<array{identifier: string, quantity: int}> $formerAssociations
     * @return array<array{identifier: string, quantity: int}>|null
     */
    private function dissociateQuantifiedProductModels(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent
    ): ?array {
        if (!$quantifiedAssociationUserIntent instanceof DissociateQuantifiedProductModels) {
            throw new \InvalidArgumentException('Unexpected user intent');
        }

        $entityIdentifiers = $quantifiedAssociationUserIntent->productModelCodes();
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
    private function replaceQuantifiedProductModels(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent,
        int $userId
    ): ?array {
        if (!$quantifiedAssociationUserIntent instanceof ReplaceAssociatedQuantifiedProductModels) {
            throw new \InvalidArgumentException('Unexpected user intent');
        }

        $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProductModels();
        $newAssociations = array_map(static fn(QuantifiedEntity $quantifiedEntity) => [
            'identifier' => $quantifiedEntity->entityIdentifier(),
            'quantity' => $quantifiedEntity->quantity(),
        ], $quantifiedEntities);

        $sortFunction = fn (array $a, array $b): int => \strcmp($a['identifier'], $b['identifier']);
        \usort($newAssociations, $sortFunction);
        \usort($formerAssociations, $sortFunction);
        if ($newAssociations === $formerAssociations) {
            return null;
        }

        $formerAssociatedIdentifiers = \array_column($formerAssociations, 'identifier');
        $viewableIdentifiers = $this->getViewableProductModels->fromProductModelCodes($formerAssociatedIdentifiers, $userId);
        $nonViewableFormerAssociations = \array_values(\array_filter(
            $formerAssociations,
            static fn (array $association): bool => !\in_array($association['identifier'], $viewableIdentifiers)
        ));

        return \array_values(\array_merge($newAssociations, $nonViewableFormerAssociations));
    }

    /**
     * @param array<array{identifier: string, quantity: int}> $formerAssociations
     * @return array<array{identifier: string, quantity: int}>|null
     */
    private function replaceQuantifiedProducts(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent,
        int $userId
    ): ?array {
        if (!$quantifiedAssociationUserIntent instanceof ReplaceAssociatedQuantifiedProducts) {
            throw new \InvalidArgumentException('Unexpected user intent');
        }

        $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProducts();
        $newNormalizedAssociations = array_map(static fn(QuantifiedEntity $quantifiedEntity) => [
            'uuid' => $quantifiedEntity->entityIdentifier(),
            'quantity' => $quantifiedEntity->quantity(),
        ], $quantifiedEntities);

        $sortFunction = fn (array $a, array $b): int => \strcmp($a['uuid'], $b['uuid']);
        \usort($newNormalizedAssociations, $sortFunction);
        \usort($formerAssociations, $sortFunction);
        if ($newNormalizedAssociations === $formerAssociations) {
            return null;
        }

        $formerAssociatedIdentifiers = array_map(
            static fn (array $formerAssociation) => Uuid::fromString($formerAssociation['uuid']),
            $formerAssociations
        );

        $viewableIdentifiers = $this->getViewableProducts->fromProductUuids($formerAssociatedIdentifiers, $userId);
        $nonViewableFormerAssociations = \array_values(\array_filter(
            $formerAssociations,
            static fn (array $association): bool => !\in_array($association['uuid'], $viewableIdentifiers)
        ));

        return \array_values(\array_merge($newNormalizedAssociations, $nonViewableFormerAssociations));
    }
}
