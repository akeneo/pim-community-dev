<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AssociationUserIntentCollection implements UserIntent
{
    /**
     * @param array<AssociationUserIntent> $associationUserIntents
     */
    public function __construct(
        private array $associationUserIntents = []
    ) {
    }

    /**
     * @return array<AssociationUserIntent>
     */
    public function associationUserIntents(): array
    {
        return $this->associationUserIntents;
    }
}
