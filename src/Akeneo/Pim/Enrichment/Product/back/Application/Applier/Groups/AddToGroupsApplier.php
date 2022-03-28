<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier\Groups;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\AddToGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\Application\Applier\UserIntentApplier;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToGroupsApplier implements UserIntentApplier
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
        Assert::isInstanceOf($groupUserIntent, AddToGroups::class);

        $formerGroupCodes = $product->getGroupCodes();
        if ([] === \array_diff($groupUserIntent->groupCodes(), $formerGroupCodes)) {
            return;
        }
        $this->productUpdater->update($product, [
            'groups' => \array_values(\array_unique(\array_merge($formerGroupCodes, $groupUserIntent->groupCodes()))),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedUserIntents(): array
    {
        return [AddToGroups::class];
    }
}
