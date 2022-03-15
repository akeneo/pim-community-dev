<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetFamily implements FamilyUserIntent
{
    public function __construct(private string $familyCode)
    {
    }

    public function familyCode(): string
    {
        return $this->familyCode;
    }
}
