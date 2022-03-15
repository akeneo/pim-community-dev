<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetEnabled implements UserIntent
{
    public function __construct(private bool $enabled)
    {
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }
}
