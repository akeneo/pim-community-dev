<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AssociationUserIntentCollection implements UserIntent
{
    public function __construct(
        private array $associationsUserIntents = []
    ) {
    }

    /**
     * @return array<AssociationsUserIntent>
     */
    public function associationsUserIntents(): array
    {
        return $this->associationsUserIntents;
    }

    /**
     * @param array<AssociationsUserIntent> $formerValues
     * @return array
     */
    public function merge(array $formerValues): array
    {
        $formattedValue = $formerValues;

        foreach ($this->associationsUserIntents() as $associationUserIntent) {
            if ($associationUserIntent instanceof AddAssociatedProducts) {
                $formattedValue[$associationUserIntent->associationType()]['products'] = \array_unique(
                    \array_merge(
                        $formattedValue[$associationUserIntent->associationType()] ?? [],
                        $associationUserIntent->productIdentifiers()
                    )
                );
            }
        }

        return $formattedValue;
    }
}
