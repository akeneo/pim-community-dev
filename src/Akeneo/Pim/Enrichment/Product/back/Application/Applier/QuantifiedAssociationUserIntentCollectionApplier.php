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
 *
 * @phpstan-type NormalizedProductAssociation array{uuid: string, quantity: int}
 * @phpstan-type NormalizedProductModelAssociation array{identifier: string, quantity: int}
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
                    $formerAssociations = $normalizedQuantifiedAssociations[$associationType][self::PRODUCTS]
                        ?? $this->getProductFormerAssociations($product, $associationType);

                    $values = $this->applyProductQuantifiedUserIntent($formerAssociations, $quantifiedAssociationUserIntent, $userId);
                    if (\is_null($values)) {
                        break 2;
                    }

                    $normalizedQuantifiedAssociations[$associationType][self::PRODUCTS] = $values;
                    break;
                case AssociateQuantifiedProductModels::class:
                case DissociateQuantifiedProductModels::class:
                case ReplaceAssociatedQuantifiedProductModels::class:
                    $formerAssociations = $normalizedQuantifiedAssociations[$associationType][self::PRODUCT_MODELS]
                        ?? $this->getProductModelFormerAssociations($product, $associationType);

                    $values = $this->applyProductModelQuantifiedUserIntent($formerAssociations, $quantifiedAssociationUserIntent, $userId);
                    if (\is_null($values)) {
                        break 2;
                    }

                    $normalizedQuantifiedAssociations[$associationType][self::PRODUCT_MODELS] = $values;
                    break;
                default: throw new \InvalidArgumentException('Unsupported association userIntent');
            }
        }

        if ([] === $normalizedQuantifiedAssociations) {
            return;
        }

        $this->productUpdater->update($product, ['quantified_associations' => $normalizedQuantifiedAssociations]);
    }

    /**
     * @return NormalizedProductAssociation[]
     */
    private function getProductFormerAssociations(
        ProductInterface $product,
        string $associationType
    ): array {
        $normalizedQuantifiedAssociations = $product->getQuantifiedAssociations()->normalize();

        return $normalizedQuantifiedAssociations[$associationType][self::PRODUCTS] ?? [];
    }

    /**
     * @return NormalizedProductModelAssociation[]
     */
    private function getProductModelFormerAssociations(
        ProductInterface $product,
        string $associationType
    ): array {
        $normalizedQuantifiedAssociations = $product->getQuantifiedAssociations()->normalize();

        return $normalizedQuantifiedAssociations[$associationType][self::PRODUCT_MODELS] ?? [];
    }

    /**
     * @param NormalizedProductAssociation[] $formerAssociations
     * @return NormalizedProductAssociation[]|null
     */
    private function associateQuantifiedProducts(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent
    ): ?array {
        Assert::isInstanceOf($quantifiedAssociationUserIntent, AssociateQuantifiedProducts::class);

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
     * @param NormalizedProductModelAssociation[] $formerAssociations
     * @return NormalizedProductModelAssociation[]|null
     */
    private function associateQuantifiedProductModels(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent
    ): ?array {
        Assert::isInstanceOf($quantifiedAssociationUserIntent, AssociateQuantifiedProductModels::class);

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
     * @param NormalizedProductAssociation[] $formerAssociations
     * @return NormalizedProductAssociation[]|null
     */
    private function dissociateQuantifiedProducts(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent
    ): ?array {
        Assert::isInstanceOf($quantifiedAssociationUserIntent, DissociateQuantifiedProducts::class);

        $entityIdentifiers = $quantifiedAssociationUserIntent->productIdentifiers();
        $newAssociations = \array_filter(
            $formerAssociations,
            static fn (array $association): bool => !\in_array($association['uuid'], $entityIdentifiers)
        );

        return \count($newAssociations) === \count($formerAssociations) ? null : \array_values($newAssociations);
    }

    /**
     * @param NormalizedProductModelAssociation[] $formerAssociations
     * @return NormalizedProductModelAssociation[]|null
     */
    private function dissociateQuantifiedProductModels(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent
    ): ?array {
        Assert::isInstanceOf($quantifiedAssociationUserIntent, DissociateQuantifiedProductModels::class);

        $entityIdentifiers = $quantifiedAssociationUserIntent->productModelCodes();
        $newAssociations = \array_filter(
            $formerAssociations,
            static fn (array $association): bool => !\in_array($association['identifier'], $entityIdentifiers)
        );

        return \count($newAssociations) === \count($formerAssociations) ? null : \array_values($newAssociations);
    }

    /**
     * @param NormalizedProductAssociation[] $formerAssociations
     * @return NormalizedProductAssociation[]|null
     */
    private function replaceQuantifiedProducts(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent,
        int $userId
    ): ?array {
        Assert::isInstanceOf($quantifiedAssociationUserIntent, ReplaceAssociatedQuantifiedProducts::class);

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

    /**
     * @param NormalizedProductModelAssociation[] $formerAssociations
     * @return NormalizedProductModelAssociation[]|null
     */
    private function replaceQuantifiedProductModels(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent,
        int $userId
    ): ?array {
        Assert::isInstanceOf($quantifiedAssociationUserIntent, ReplaceAssociatedQuantifiedProductModels::class);

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
     * @param NormalizedProductAssociation[] $formerAssociations
     * @return NormalizedProductAssociation[]|null
     */
    private function applyProductQuantifiedUserIntent(array $formerAssociations, QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent, int $userId)
    {
        return match ($quantifiedAssociationUserIntent::class) {
            AssociateQuantifiedProducts::class =>
                $this->associateQuantifiedProducts(
                    $formerAssociations,
                    $quantifiedAssociationUserIntent
                ),
            DissociateQuantifiedProducts::class =>
                $this->dissociateQuantifiedProducts(
                    $formerAssociations,
                    $quantifiedAssociationUserIntent
                ),
            ReplaceAssociatedQuantifiedProducts::class =>
                $this->replaceQuantifiedProducts(
                    $formerAssociations,
                    $quantifiedAssociationUserIntent,
                    $userId
                ),
            default => throw new \InvalidArgumentException('Unsupported association userIntent')
        };
    }

    /**
     * @param NormalizedProductModelAssociation[] $formerAssociations
     * @return NormalizedProductModelAssociation[]|null
     */
    private function applyProductModelQuantifiedUserIntent(array $formerAssociations, QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent, int $userId)
    {
        return match ($quantifiedAssociationUserIntent::class) {
            AssociateQuantifiedProductModels::class =>
                $this->associateQuantifiedProductModels(
                    $formerAssociations,
                    $quantifiedAssociationUserIntent
                ),
            DissociateQuantifiedProductModels::class =>
                $this->dissociateQuantifiedProductModels(
                    $formerAssociations,
                    $quantifiedAssociationUserIntent
                ),
            ReplaceAssociatedQuantifiedProductModels::class =>
                $this->replaceQuantifiedProductModels(
                    $formerAssociations,
                    $quantifiedAssociationUserIntent,
                    $userId
                ),
            default => throw new \InvalidArgumentException('Unsupported association userIntent')
        };
    }
}
