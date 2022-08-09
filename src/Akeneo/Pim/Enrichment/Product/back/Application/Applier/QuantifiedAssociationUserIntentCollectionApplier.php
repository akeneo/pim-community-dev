<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\DissociateQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedProductWithUuid;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QuantifiedAssociationUserIntentCollectionApplier implements UserIntentApplier
{
    private const PRODUCTS = 'products';
    private const PRODUCT_UUIDS = 'product_uuids';
    private const PRODUCT_MODELS = 'product_models';

    public function __construct(
        private ObjectUpdaterInterface $productUpdater,
        private GetViewableProducts $getViewableProducts,
        private GetViewableProductModels $getViewableProductModels,
        private Connection $connection,
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
                AssociateQuantifiedProducts::class, AssociateQuantifiedProductModels::class, AssociateQuantifiedProductUuids::class =>
                    $this->associateQuantifiedEntities(
                        $formerAssociations,
                        $quantifiedAssociationUserIntent,
                    ),
                DissociateQuantifiedProducts::class, DissociateQuantifiedProductModels::class, DissociateQuantifiedProductUuids::class =>
                    $this->dissociateQuantifiedEntities(
                        $formerAssociations,
                        $quantifiedAssociationUserIntent
                    ),
                ReplaceAssociatedQuantifiedProducts::class, ReplaceAssociatedQuantifiedProductModels::class, ReplaceAssociatedQuantifiedProductUuids::class =>
                    $this->replaceQuantifiedEntities(
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
     * @return array<array{identifier: string, quantity: int}|array{uuid: string, quantity: int}>
     */
    private function getFormerAssociations(
        QuantifiedAssociationUserIntent $associationUserIntent,
        ProductInterface $product,
        string $entityType
    ): array {
        $normalizedQuantifiedAssociations = $product->getQuantifiedAssociations()->normalize();

        if ($entityType === self::PRODUCT_UUIDS) {
            $identifiers = \array_map(
                fn ($quantifiedAssociation) => $quantifiedAssociation['identifier'],
                $normalizedQuantifiedAssociations[$associationUserIntent->associationType()][self::PRODUCTS]
            );
            $indexedUuidsByIdentifier = $this->getProductUuidsFromIdentifiers($identifiers);

            return \array_map(fn ($data) => [
                'uuid' => $indexedUuidsByIdentifier[$data['identifier']],
                'quantity' => (int) $data['quantity']
            ], $normalizedQuantifiedAssociations[$associationUserIntent->associationType()][self::PRODUCTS] ?? []);
        }

        return $normalizedQuantifiedAssociations[$associationUserIntent->associationType()][$entityType] ?? [];
    }

    private function getAssociationEntityType(QuantifiedAssociationUserIntent $userIntent): string
    {
        return match ($userIntent::class) {
            AssociateQuantifiedProducts::class, DissociateQuantifiedProducts::class, ReplaceAssociatedQuantifiedProducts::class
                => self::PRODUCTS,
            AssociateQuantifiedProductModels::class, DissociateQuantifiedProductModels::class, ReplaceAssociatedQuantifiedProductModels::class
                => self::PRODUCT_MODELS,
            AssociateQuantifiedProductUuids::class, DissociateQuantifiedProductUuids::class, ReplaceAssociatedQuantifiedProductUuids::class
                => self::PRODUCT_UUIDS,
            default => throw new \LogicException('User intent cannot be handled')
        };
    }

    /**
     * @param array<array{identifier: string, quantity: int}|array{uuid: string, quantity: int}> $formerAssociations
     * @return array<array{identifier: string, quantity: int}|array{uuid: string, quantity: int}>|null
     */
    private function associateQuantifiedEntities(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent,
    ): ?array {
        if ($quantifiedAssociationUserIntent instanceof AssociateQuantifiedProducts
            || $quantifiedAssociationUserIntent instanceof AssociateQuantifiedProductUuids) {
            $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProducts();
        } elseif ($quantifiedAssociationUserIntent instanceof AssociateQuantifiedProductModels) {
            $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProductModels();
        } else {
            throw new \InvalidArgumentException('Unexpected user intent');
        }
        $indexedFormerAssociations = [];
        foreach ($formerAssociations as $formerAssociation) {
            if (\array_key_exists('uuid', $formerAssociation)) {
                $indexedFormerAssociations[$formerAssociation['uuid']] = $formerAssociation;
            } else {
                $indexedFormerAssociations[$formerAssociation['identifier']] = $formerAssociation;
            }
        }

        $isUpdated = false;
        foreach ($quantifiedEntities as $quantifiedEntity) {
            if ($quantifiedEntity instanceof QuantifiedProductWithUuid) {
                $indexValue = $quantifiedEntity->productUuid()->toString();
            } else {
                $indexValue = $quantifiedEntity->entityIdentifier();
            }

            if (\array_key_exists($indexValue, $indexedFormerAssociations)
                && $indexedFormerAssociations[$indexValue]['quantity'] === $quantifiedEntity->quantity()
            ) {
                continue;
            }

            if ($quantifiedEntity instanceof QuantifiedProductWithUuid) {
                $indexedFormerAssociations[$indexValue] = [
                    'uuid' => (string) $indexValue,
                    'quantity' => (int) $quantifiedEntity->quantity()
                ];
            } else {
                $indexedFormerAssociations[$indexValue] = [
                    'identifier' => (string) $indexValue,
                    'quantity' => (int) $quantifiedEntity->quantity()
                ];
            }

            $isUpdated = true;
        }

        if (!$isUpdated) {
            return null;
        }

        return \array_values($indexedFormerAssociations);
    }

    /**
     * @param array<array{identifier: string, quantity: int}|array{uuid: string, quantity: int}> $formerAssociations
     * @return array<array{identifier: string, quantity: int}|array{uuid: string, quantity: int}>|null
     */
    private function dissociateQuantifiedEntities(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent
    ): ?array {
        if ($quantifiedAssociationUserIntent instanceof DissociateQuantifiedProductUuids) {
            $uuidsAsString = \array_map(fn (UuidInterface $uuid): string => $uuid->toString(), $quantifiedAssociationUserIntent->productUuids());
            $newAssociations = \array_filter(
                $formerAssociations,
                static fn (array $association): bool => !\in_array($association['uuid'] ?? null, $uuidsAsString)
            );

            return \count($newAssociations) === \count($formerAssociations) ? null : \array_values($newAssociations);
        }

        if ($quantifiedAssociationUserIntent instanceof DissociateQuantifiedProducts) {
            $entityIdentifiers = $quantifiedAssociationUserIntent->productIdentifiers();
        } elseif ($quantifiedAssociationUserIntent instanceof DissociateQuantifiedProductModels) {
            $entityIdentifiers = $quantifiedAssociationUserIntent->productModelCodes();
        } else {
            throw new \InvalidArgumentException('Unexpected user intent');
        }
        $newAssociations = \array_filter(
            $formerAssociations,
            static fn (array $association): bool => !\in_array($association['identifier'] ?? null, $entityIdentifiers)
        );

        return \count($newAssociations) === \count($formerAssociations) ? null : \array_values($newAssociations);
    }

    /**
     * @param array<array{identifier: string, quantity: int}|array{uuid: string, quantity: int}> $formerAssociations
     * @return array<array{identifier: string, quantity: int}|array{uuid: string, quantity: int}>|null
     */
    private function replaceQuantifiedEntities(
        array $formerAssociations,
        QuantifiedAssociationUserIntent $quantifiedAssociationUserIntent,
        int $userId
    ): ?array {
        if ($quantifiedAssociationUserIntent instanceof ReplaceAssociatedQuantifiedProducts) {
            $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProducts();
        } elseif ($quantifiedAssociationUserIntent instanceof ReplaceAssociatedQuantifiedProductModels) {
            $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProductModels();
        } elseif ($quantifiedAssociationUserIntent instanceof ReplaceAssociatedQuantifiedProductUuids) {
            $quantifiedEntities = $quantifiedAssociationUserIntent->quantifiedProducts();
        } else {
            throw new \InvalidArgumentException('Unexpected user intent');
        }

        $newAssociations = [];

        if ($quantifiedAssociationUserIntent instanceof ReplaceAssociatedQuantifiedProductUuids) {
            /** @var QuantifiedProductWithUuid $quantifiedProduct */
            foreach ($quantifiedEntities as $quantifiedProduct) {
                $newAssociations[] = [
                    'uuid' => $quantifiedProduct->productUuid()->toString(),
                    'quantity' => $quantifiedProduct->quantity(),
                ];
            }
        } else {
            /** @var QuantifiedEntity $quantifiedEntity */
            foreach ($quantifiedEntities as $quantifiedEntity) {
                $newAssociations[] = [
                    'identifier' => $quantifiedEntity->entityIdentifier(),
                    'quantity' => $quantifiedEntity->quantity(),
                ];
            }
            $sortFunction = fn (array $a, array $b): int => \strcmp($a['identifier'], $b['identifier']);
            \usort($newAssociations, $sortFunction);
            \usort($formerAssociations, $sortFunction);
        }

        if ($newAssociations === $formerAssociations) {
            return null;
        }

        $formerAssociatedIdentifiers = \array_column($formerAssociations, 'identifier');
        $viewableUuids = [];
        $viewableIdentifiers = [];
        if ($quantifiedAssociationUserIntent instanceof ReplaceAssociatedQuantifiedProducts) {
            $viewableIdentifiers = $this->getViewableProducts->fromProductIdentifiers($formerAssociatedIdentifiers, $userId);
        } elseif ($quantifiedAssociationUserIntent instanceof ReplaceAssociatedQuantifiedProductUuids) {
            $formerAssociatedUuids = \array_column($formerAssociations, 'uuid');
            $viewableUuids = $this->getViewableProducts->fromProductUuids($formerAssociatedUuids, $userId);
        } elseif ($quantifiedAssociationUserIntent instanceof ReplaceAssociatedQuantifiedProductModels) {
            $viewableIdentifiers = $this->getViewableProductModels->fromProductModelCodes($formerAssociatedIdentifiers, $userId);
        }

        if ($quantifiedAssociationUserIntent instanceof ReplaceAssociatedQuantifiedProductUuids) {
            $nonViewableFormerAssociations = \array_values(\array_filter(
                $formerAssociations,
                static fn (array $association): bool => !\in_array($association['uuid'] ?? null, $viewableUuids)
            ));
        } else {
            $nonViewableFormerAssociations = \array_values(\array_filter(
                $formerAssociations,
                static fn (array $association): bool => !\in_array($association['identifier'] ?? null, $viewableIdentifiers)
            ));
        }

        return \array_values(\array_merge($newAssociations, $nonViewableFormerAssociations));
    }

    /**
     * @param string[] $identifiers
     * @return array<string, string>
     */
    private function getProductUuidsFromIdentifiers(array $identifiers): array
    {
        $result = $this->connection->fetchAllAssociative(
            'SELECT BIN_TO_UUID(uuid) as uuid, identifier FROM pim_catalog_product WHERE identifier in (?)',
            [$identifiers]
        );

        $indexedUuidByIdentifier = [];
        foreach ($result as $data) {
            $indexedUuidByIdentifier[(string) $data['identifier']] = $data['uuid'];
        }
        return $indexedUuidByIdentifier;
    }
}
