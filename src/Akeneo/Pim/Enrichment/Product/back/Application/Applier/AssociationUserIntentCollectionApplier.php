<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddAssociatedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AssociationsUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AssociationUserIntentCollectionApplier implements UserIntentApplier
{
    public function __construct(
        private ObjectUpdaterInterface $productUpdater,
    ) {
    }

    public function apply(UserIntent $userIntent, ProductInterface $product, int $userId): void
    {
        Assert::isInstanceOf($userIntent, AssociationUserIntentCollection::class);
        $normalizedAssociations = [];

        foreach ($userIntent->associationsUserIntents() as $associationUserIntent) {
            $formerAssociations = $this->getFormerAssociations($associationUserIntent, $normalizedAssociations, $product);
            if ($associationUserIntent instanceof AddAssociatedProducts) {
                if (\count(\array_diff($associationUserIntent->productIdentifiers(), $formerAssociations)) === 0) {
                    continue;
                }
                $normalizedAssociations[$associationUserIntent->associationType()]['products'] = \array_values(
                    \array_unique(
                        \array_merge($formerAssociations, $associationUserIntent->productIdentifiers())
                    )
                );
            }
        }

        if ([] === $normalizedAssociations) {
            return;
        }

        $this->productUpdater->update($product, ['associations' => $normalizedAssociations]);
    }

    public function getSupportedUserIntents(): array
    {
        return [AssociationUserIntentCollection::class];
    }

    private function getFormerAssociations(AssociationsUserIntent $associationUserIntent, array $normalizedAssociations, ProductInterface $product): array
    {
        if ($associationUserIntent instanceof AddAssociatedProducts) {
            return $normalizedAssociations[$associationUserIntent->associationType()]['products'] ??
                $product
                    ->getAssociatedProducts($associationUserIntent->associationType())
                    ?->map(fn (ProductInterface $product): string => $product->getIdentifier())?->toArray() ?? [];
        }
        throw new \LogicException('Not implemented');
    }
}
