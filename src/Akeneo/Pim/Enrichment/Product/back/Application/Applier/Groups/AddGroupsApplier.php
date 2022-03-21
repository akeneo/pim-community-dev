<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier\Groups;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\AddGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddGroupsApplier implements UserIntentApplier
{
    public function __construct(
        private ObjectUpdaterInterface $productUpdater
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function apply(UserIntent $groupsUserIntent, ProductInterface $product, int $userId): void
    {
        Assert::isInstanceOf($groupsUserIntent, AddGroups::class);

        $existingGroups = $product->getGroupCodes();
        $newGroups = \array_values(\array_unique(\array_merge($existingGroups, $groupsUserIntent->groupCodes())));

        if (\count(\array_diff($newGroups, $existingGroups)) > 0) {
            $this->productUpdater->update($product, [
                'groups' => $newGroups,
            ]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedUserIntents(): array
    {
        return [AddGroups::class];
    }
}
