<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier\Groups;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\RemoveFromGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveFromGroupsApplier implements UserIntentApplier
{
    public function __construct(
        private ObjectUpdaterInterface $productUpdater
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function apply(UserIntent $groupUserIntent, ProductInterface $product, int $userId): void
    {
        Assert::isInstanceOf($groupUserIntent, RemoveFromGroups::class);

        $formerGroupCodes = \array_values($product->getGroupCodes());
        $updatedGroupCodes = \array_diff($formerGroupCodes, $groupUserIntent->groupCodes());
        if ($formerGroupCodes === $updatedGroupCodes) {
            return;
        }
        $this->productUpdater->update($product, [
            'groups' => \array_values($updatedGroupCodes),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedUserIntents(): array
    {
        return [RemoveFromGroups::class];
    }
}
