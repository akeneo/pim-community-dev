<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;

/**
 * Interface meant for applying user intents on products
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UserIntentApplier
{
    public function apply(UserIntent $userIntent, ProductInterface $product, int $userId): void;

    /**
     * @return array<class-string>
     */
    public function getSupportedUserIntents(): array;
}
