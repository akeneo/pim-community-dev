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
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @phpstan-type NormalizedProductQuantifiedLink array{uuid: string | null, identifier: string | null, quantity: int}
 * @phpstan-type NormalizedProductModelQuantifiedLink array{identifier: string, quantity: int}
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
            switch ($quantifiedAssociationUserIntent::class) {
                case AssociateQuantifiedProducts::class:
                case DissociateQuantifiedProducts::class:
                case ReplaceAssociatedQuantifiedProducts::class:
                case ReplaceAssociatedQuantifiedProductUuids::class:
                    $formerAssociations = $normalizedQuantifiedAssociations[$associationType][self::PRODUCTS]
                        ?? $this->getProductQuantifiedLinks($product, $associationType);

                    $values = $this->applyProductQuantifiedUserIntent($formerAssociations, $quantifiedAssociationUserIntent, $userId);

                    if (\is_null($values)) {
                        break;
                    }

                    $normalizedQuantifiedAssociations[$associationType][self::PRODUCTS] = $values;
                    break;
                case AssociateQuantifiedProductModels::class:
                case DissociateQuantifiedProductModels::class:
                case ReplaceAssociatedQuantifiedProductModels::class:
                    $formerAssociations = $normalizedQuantifiedAssociations[$associationType][self::PRODUCT_MODELS]
                        ?? $this->getProductModelQuantifiedLinks($product, $associationType);

                    $values = $this->applyProductModelQuantifiedUserIntent($formerAssociations, $quantifiedAssociationUserIntent, $userId);
                    if (\is_null($values)) {
                        break;
                    }

                    $normalizedQuantifiedAssociations[$associationType][self::PRODUCT_MODELS] = $values;
                    break;
                default:
                    throw new \InvalidArgumentException('Unsupported association userIntent');
            }
        }

        if (empty($normalizedQuantifiedAssociations)) {
            return;
        }

        $this->productUpdater->update($product, ['quantified_associations' => $normalizedQuantifiedAssociations]);
    }

    /**
     * @return NormalizedProductQuantifiedLink[]
     */
    private function getProductQuantifiedLinks(
        ProductInterface $product,
        string $associationType
    ): array {
        $normalizedQuantifiedAssociations = $product->getQuantifiedAssociations()->normalize();

        return $normalizedQuantifiedAssociations[$associationType][self::PRODUCTS] ?? [];
    }

    /**
     * @return NormalizedProductModelQuantifiedLink[]
     */
    private function getProductModelQuantifiedLinks(
        ProductInterface $product,
        string $associationType
    ): array {
        $normalizedQuantifiedAssociations = $product->getQuantifiedAssociations()->normalize();

        return $normalizedQuantifiedAssociations[$associationType][self::PRODUCT_MODELS] ?? [];
    }

    /**
     * @param NormalizedProductQuantifiedLink[] $formerAssociations
     * @return NormalizedProductQuantifiedLink[]|null
     */
    private function associateQuantifiedProducts(
        array $formerAssociations,
        AssociateQuantifiedProducts $quantifiedAssociationUserIntent
    ): ?array {
        $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProducts();

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
                'uuid' => $indexedFormerAssociations[$identifier]['uuid'] ?? null,
                'quantity' => $quantifiedEntity->quantity(),
            ];
            $isUpdated = true;
        }

        return $isUpdated ? \array_values($indexedFormerAssociations) : null;
    }

    /**
     * @param NormalizedProductModelQuantifiedLink[] $formerAssociations
     * @return NormalizedProductModelQuantifiedLink[]|null
     */
    private function associateQuantifiedProductModels(
        array $formerAssociations,
        AssociateQuantifiedProductModels $quantifiedAssociationUserIntent
    ): ?array {
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
     * @param NormalizedProductQuantifiedLink[] $formerAssociations
     * @return NormalizedProductQuantifiedLink[]|null
     */
    private function dissociateQuantifiedProducts(
        array $formerAssociations,
        DissociateQuantifiedProducts $quantifiedAssociationUserIntent
    ): ?array {
        $entityIdentifiers = $quantifiedAssociationUserIntent->productIdentifiers();
        $newAssociations = \array_filter(
            $formerAssociations,
            static fn (array $association): bool => !\in_array($association['identifier'], $entityIdentifiers)
        );

        return \count($newAssociations) === \count($formerAssociations) ? null : \array_values($newAssociations);
    }

    /**
     * @param NormalizedProductModelQuantifiedLink[] $formerAssociations
     * @return NormalizedProductModelQuantifiedLink[]|null
     */
    private function dissociateQuantifiedProductModels(
        array $formerAssociations,
        DissociateQuantifiedProductModels $quantifiedAssociationUserIntent
    ): ?array {
        $entityIdentifiers = $quantifiedAssociationUserIntent->productModelCodes();

        $newAssociations = \array_filter(
            $formerAssociations,
            static fn (array $association): bool => !\in_array($association['identifier'], $entityIdentifiers)
        );

        return \count($newAssociations) === \count($formerAssociations) ? null : \array_values($newAssociations);
    }

    /**
     * @param NormalizedProductQuantifiedLink[] $formerAssociations
     * @return NormalizedProductQuantifiedLink[]|null
     */
    private function replaceQuantifiedProducts(
        array $formerAssociations,
        ReplaceAssociatedQuantifiedProducts $quantifiedAssociationUserIntent,
        int $userId
    ): ?array {
        $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProducts();

        $newAssociations = array_map(static fn (QuantifiedEntity $quantifiedEntity) => [
            'identifier' => $quantifiedEntity->entityIdentifier(),
            'uuid' => null,
            'quantity' => $quantifiedEntity->quantity(),
        ], $quantifiedEntities);

        $sortFunction = fn (array $a, array $b): int => \strcmp($a['identifier'], $b['identifier']);
        \usort($newAssociations, $sortFunction);
        \usort($formerAssociations, $sortFunction);
        if ($newAssociations === $formerAssociations) {
            /**
             * @TODO CPM-1208 There is an edge case here.
             * - create a product with a ReplaceQuantifiedProducts and an invalid association type and nothing inside.
             * - The former associations (as the product is new) are [].
             * - There is no difference between the previous one ([]) and the new one ([]).
             * - No error is raised, even if the user wrote a non existing association type.
             */
            return null;
        }

        $formerAssociatedIdentifiers = \array_column($formerAssociations, 'identifier');
        $formerAssociatedIdentifiers = array_filter(
            $formerAssociatedIdentifiers,
            static fn ($formerAssociatedIdentifier) => $formerAssociatedIdentifier !== null
        );
        $viewableIdentifiers = $this->getViewableProducts->fromProductIdentifiers($formerAssociatedIdentifiers, $userId);
        $nonViewableFormerAssociations = \array_values(\array_filter(
            $formerAssociations,
            static fn (array $association): bool => !\in_array($association['identifier'], $viewableIdentifiers)
        ));

        return \array_values(\array_merge($newAssociations, $nonViewableFormerAssociations));
    }

    /**
     * @param NormalizedProductModelQuantifiedLink[] $formerAssociations
     * @return NormalizedProductModelQuantifiedLink[]|null
     */
    private function replaceQuantifiedProductModels(
        array $formerAssociations,
        ReplaceAssociatedQuantifiedProductModels $quantifiedAssociationUserIntent,
        int $userId
    ): ?array {
        $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProductModels();

        $newAssociations = array_map(static fn (QuantifiedEntity $quantifiedEntity) => [
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
     * @param NormalizedProductQuantifiedLink[] $formerAssociations
     * @return NormalizedProductQuantifiedLink[]|null
     */
    private function replaceQuantifiedProductUuids(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent,
        int $userId
    ): ?array {
        Assert::isInstanceOf($quantifiedAssociationUserIntent, ReplaceAssociatedQuantifiedProductUuids::class);
        $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProducts();

        $newAssociations = [];
        /** @var QuantifiedEntity $quantifiedEntity */
        foreach ($quantifiedEntities as $quantifiedEntity) {
            $newAssociations[] = [
                'uuid' => $quantifiedEntity->entityIdentifier(),
                'identifier' => null,
                'quantity' => $quantifiedEntity->quantity(),
            ];
        }

        $sortFunction = fn (array $a, array $b): int => \strcmp($a['uuid'], $b['uuid']);
        \usort($newAssociations, $sortFunction);
        \usort($formerAssociations, $sortFunction);
        if ($newAssociations === $formerAssociations) {
            return null;
        }

        $formerAssociatedUuids = \array_column($formerAssociations, 'uuid');
        $formerAssociatedUuids = array_filter(
            $formerAssociatedUuids,
            static fn ($formerAssociatedUuid) => $formerAssociatedUuid !== null
        ) ?: [];

        $formerAssociatedUuids = \array_map(
            static fn (string $formerAssociatedUuid) => Uuid::fromString($formerAssociatedUuid),
            $formerAssociatedUuids
        );

        $viewableUuids = $this->getViewableProducts->fromProductUuids($formerAssociatedUuids, $userId);
        $nonViewableFormerAssociations = \array_values(\array_filter(
            $formerAssociations,
            static fn (array $association): bool => !\in_array($association['uuid'], $viewableUuids)
        ));

        return \array_values(\array_merge($newAssociations, $nonViewableFormerAssociations));
    }

    /**
     * @param NormalizedProductQuantifiedLink[] $formerAssociations
     * @return NormalizedProductQuantifiedLink[]|null
     */
    private function applyProductQuantifiedUserIntent(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent,
        int $userId
    ): ?array {
        if ($quantifiedAssociationUserIntent instanceof AssociateQuantifiedProducts) {
            return $this->associateQuantifiedProducts($formerAssociations, $quantifiedAssociationUserIntent);
        } elseif ($quantifiedAssociationUserIntent instanceof DissociateQuantifiedProducts) {
            return $this->dissociateQuantifiedProducts($formerAssociations, $quantifiedAssociationUserIntent);
        } elseif ($quantifiedAssociationUserIntent instanceof ReplaceAssociatedQuantifiedProducts) {
            return $this->replaceQuantifiedProducts($formerAssociations, $quantifiedAssociationUserIntent, $userId);
        } elseif ($quantifiedAssociationUserIntent instanceof ReplaceAssociatedQuantifiedProductUuids) {
            return $this->replaceQuantifiedProductUuids($formerAssociations, $quantifiedAssociationUserIntent, $userId);
        }

        throw new \InvalidArgumentException('Unsupported association userIntent');
    }

    /**
     * @param NormalizedProductModelQuantifiedLink[] $formerAssociations
     * @return NormalizedProductModelQuantifiedLink[]|null
     */
    private function applyProductModelQuantifiedUserIntent(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent,
        int $userId
    ): ?array {
        if ($quantifiedAssociationUserIntent instanceof AssociateQuantifiedProductModels) {
            return $this->associateQuantifiedProductModels($formerAssociations, $quantifiedAssociationUserIntent);
        } elseif ($quantifiedAssociationUserIntent instanceof DissociateQuantifiedProductModels) {
            return $this->dissociateQuantifiedProductModels($formerAssociations, $quantifiedAssociationUserIntent);
        } elseif ($quantifiedAssociationUserIntent instanceof ReplaceAssociatedQuantifiedProductModels) {
            return $this->replaceQuantifiedProductModels($formerAssociations, $quantifiedAssociationUserIntent, $userId);
        }

        throw new \InvalidArgumentException('Unsupported association userIntent');
    }
}
