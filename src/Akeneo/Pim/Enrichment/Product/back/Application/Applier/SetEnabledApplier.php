<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetEnabledApplier implements UserIntentApplier
{
    /**
     * {@inheritDoc}
     */
    public function apply(UserIntent $userIntent, ProductInterface $product, int $userId): void
    {
        Assert::isInstanceOf($userIntent, SetEnabled::class);
        $product->setEnabled($userIntent->enabled());
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedUserIntents(): array
    {
        return [SetEnabled::class];
    }
}
