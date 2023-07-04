<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProductModels;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetViewableProducts;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AssociationUserIntentCollectionApplier implements UserIntentApplier
{
    private const PRODUCTS = 'products';
    private const PRODUCT_UUIDS = 'product_uuids';
    private const PRODUCT_MODELS = 'product_models';
    private const GROUPS = 'groups';

    public function __construct(
        private ObjectUpdaterInterface $productUpdater,
        private GetViewableProducts $getViewableProducts,
        private GetViewableProductModels $getViewableProductModels
    ) {
    }

    public function apply(UserIntent $userIntent, ProductInterface $product, int $userId): void
    {
        Assert::isInstanceOf($userIntent, AssociationUserIntentCollection::class);
        $normalizedAssociations = [];

        foreach ($userIntent->associationUserIntents() as $associationUserIntent) {
            $entityType = $this->getAssociationEntityType($associationUserIntent);
            $formerAssociations = $this->getFormerAssociations($associationUserIntent, $normalizedAssociations, $product, $entityType);
            $entityAssociations = $this->userIntentEntityAssociations($associationUserIntent, $entityType);

            $values = match ($associationUserIntent::class) {
                AssociateProducts::class, AssociateProductModels::class, AssociateGroups::class =>
                    $this->associateEntities($formerAssociations, $entityAssociations),
                DissociateProducts::class, DissociateProductModels::class, DissociateGroups::class =>
                    $this->dissociateEntities($formerAssociations, $entityAssociations),
                ReplaceAssociatedProducts::class, ReplaceAssociatedProductModels::class, ReplaceAssociatedGroups::class =>
                    $this->replaceAssociatedEntities($formerAssociations, $entityAssociations, $entityType, $userId),
                ReplaceAssociatedProductUuids::class =>
                    $this->replaceAssociatedProductsByUuid($formerAssociations, $entityAssociations, $userId),
                default => throw new \InvalidArgumentException('Unsupported association userIntent')
            };
            if (\is_null($values)) {
                continue;
            }
            $normalizedAssociations[$associationUserIntent->associationType()][$entityType] = $values;
        }

        // [legacy] all the associations are saved in the database (even empty),
        // in the future, it would be better to add the missing associations in the normalization,

        // if ([] === $normalizedAssociations) {
        //   return;
        // }

        $this->productUpdater->update($product, ['associations' => $normalizedAssociations]);
    }

    /** {@inheritDoc} */
    public function getSupportedUserIntents(): array
    {
        return [AssociationUserIntentCollection::class];
    }

    /**
     * @param AssociationUserIntent $associationUserIntent
     * @param array<string, array<string, array<string>>> $normalizedAssociations
     * @param ProductInterface $product
     * @param string $entityType
     *
     * @return array<string>
     */
    private function getFormerAssociations(AssociationUserIntent $associationUserIntent, array $normalizedAssociations, ProductInterface $product, string $entityType): array
    {
        $values = match ($entityType) {
            self::PRODUCTS => $product
                    ->getAssociatedProducts($associationUserIntent->associationType())
                    ?->map(fn (ProductInterface $product): string => $product->getIdentifier())?->toArray() ?? [],
            self::PRODUCT_UUIDS => $product
                    ->getAssociatedProducts($associationUserIntent->associationType())
                    ?->map(fn (ProductInterface $product): string => $product->getUuid()->toString())?->toArray() ?? [],
            self::PRODUCT_MODELS => $product
                    ->getAssociatedProductModels($associationUserIntent->associationType())
                    ?->map(fn (ProductModelInterface $productModel): string => $productModel->getIdentifier())?->toArray() ?? [],
            self::GROUPS => $product
                    ->getAssociatedGroups($associationUserIntent->associationType())
                    ?->map(fn (GroupInterface $group): string => $group->getCode())?->toArray() ?? [],
            default => throw new \LogicException('Not implemented')
        };

        return $normalizedAssociations[$associationUserIntent->associationType()][$entityType] ?? $values;
    }

    private function getAssociationEntityType(AssociationUserIntent $userIntent): string
    {
        return match ($userIntent::class) {
            AssociateProducts::class, DissociateProducts::class, ReplaceAssociatedProducts::class => self::PRODUCTS,
            AssociateProductModels::class, DissociateProductModels::class, ReplaceAssociatedProductModels::class => self::PRODUCT_MODELS,
            AssociateGroups::class, DissociateGroups::class, ReplaceAssociatedGroups::class => self::GROUPS,
            ReplaceAssociatedProductUuids::class => self::PRODUCT_UUIDS,
            default => throw new \LogicException('User intent cannot be handled')
        };
    }

    /**
     * @return array<string>
     */
    private function userIntentEntityAssociations(AssociationUserIntent $associationUserIntent, string $entityType): array
    {
        if ($entityType === self::PRODUCTS && \method_exists($associationUserIntent, 'productIdentifiers')) {
            return $associationUserIntent->productIdentifiers();
        } elseif ($entityType === self::PRODUCT_UUIDS && \method_exists($associationUserIntent, 'productUuids')) {
            return $associationUserIntent->productUuids();
        } elseif ($entityType === self::PRODUCT_MODELS && \method_exists($associationUserIntent, 'productModelCodes')) {
            return $associationUserIntent->productModelCodes();
        } elseif ($entityType === self::GROUPS && \method_exists($associationUserIntent, 'groupCodes')) {
            return $associationUserIntent->groupCodes();
        }
        throw new \LogicException('Not Implemented');
    }

    /**
     * @param array<string> $formerAssociations
     * @param array<string> $entityAssociations
     * @return array<string>|null
     */
    private function associateEntities(array $formerAssociations, array $entityAssociations): ?array
    {
        if (\count(\array_diff($entityAssociations, $formerAssociations)) === 0) {
            return null;
        }
        return \array_values(\array_unique(\array_merge($formerAssociations, $entityAssociations)));
    }

    /**
     * @param array<string> $formerAssociations
     * @param array<string> $entityAssociations
     * @return array<string>|null
     */
    private function dissociateEntities(array $formerAssociations, array $entityAssociations): ?array
    {
        $newAssociations = \array_diff($formerAssociations, $entityAssociations);
        if (\count($newAssociations) === \count($formerAssociations)) {
            return null;
        }

        return \array_values($newAssociations);
    }

    /**
     * @param array<string> $formerAssociations
     * @param array<string> $entityAssociations
     * @param string $entityType
     * @param int $userId
     * @return array<string>|null
     */
    private function replaceAssociatedEntities(array $formerAssociations, array $entityAssociations, string $entityType, int $userId): ?array
    {
        $newEntityAssociations = $entityAssociations;
        \sort($formerAssociations);
        \sort($newEntityAssociations);
        if ($newEntityAssociations === $formerAssociations) {
            return null;
        }

        $nonViewableEntities = [];
        if (self::PRODUCTS === $entityType) {
            $viewableProductIdentifiers = $this->getViewableProducts->fromProductIdentifiers($formerAssociations, $userId);
            $nonViewableEntities = \array_values(\array_diff($formerAssociations, $viewableProductIdentifiers));
        } elseif (self::PRODUCT_MODELS === $entityType) {
            $viewableProductModels = $this->getViewableProductModels->fromProductModelCodes($formerAssociations, $userId);
            $nonViewableEntities = \array_values(\array_diff($formerAssociations, $viewableProductModels));
        }

        return \array_values(\array_unique(\array_merge($nonViewableEntities, $entityAssociations)));
    }

    /**
     * @param array<string> $formerAssociations
     * @param array<string> $entityAssociations
     * @param int $userId
     * @return array<string>|null
     */
    private function replaceAssociatedProductsByUuid(array $formerAssociations, array $entityAssociations, int $userId): ?array
    {
        $newEntityAssociations = $entityAssociations;
        \sort($formerAssociations);
        \sort($newEntityAssociations);
        if ($newEntityAssociations === $formerAssociations) {
            return null;
        }

        $uuids = array_map(fn (string $uuid): UuidInterface => Uuid::fromString($uuid), $formerAssociations);

        $viewableProductUuids = $this->getViewableProducts->fromProductUuids($uuids, $userId);
        $viewableProductUuidsAsStr = \array_map(fn (UuidInterface $uuid): string => $uuid->toString(), $viewableProductUuids);
        $nonViewableEntities = \array_values(\array_diff($formerAssociations, $viewableProductUuidsAsStr));

        return \array_values(\array_unique(\array_merge($nonViewableEntities, $entityAssociations)));
    }
}
