<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedProduct;
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
                AssociateQuantifiedProducts::class => $this->associateQuantifiedEntities(
                    $formerAssociations,
                    $quantifiedAssociationUserIntent->quantifiedProducts()
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
            AssociateQuantifiedProducts::class => self::PRODUCTS,
            default => throw new \LogicException('User intent cannot be handled')
        };
    }

    /**
     * @param array<array{identifier: string, quantity: int}> $formerAssociations
     * @param QuantifiedProduct[] $newQuantifiedProducts
     * @return array<array{identifier: string, quantity: int}>|null
     */
    private function associateQuantifiedEntities(array $formerAssociations, array $newQuantifiedProducts): ?array
    {
        $indexedFormerAssociations = [];
        foreach ($formerAssociations as $formerAssociation) {
            $indexedFormerAssociations[$formerAssociation['identifier']] = $formerAssociation;
        }

        $isUpdated = false;
        foreach ($newQuantifiedProducts as $quantifiedProduct) {
            $identifier = $quantifiedProduct->productIdentifier();
            if (\array_key_exists($identifier, $indexedFormerAssociations)
                && $indexedFormerAssociations[$identifier]['quantity'] === $quantifiedProduct->quantity()
            ) {
                continue;
            }

            $indexedFormerAssociations[$identifier] = [
                'identifier' => $identifier,
                'quantity' => $quantifiedProduct->quantity(),
            ];
            $isUpdated = true;
        }

        if (!$isUpdated) {
            return null;
        }

        return \array_values($indexedFormerAssociations);
    }
}
