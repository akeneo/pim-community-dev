<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\MainIdentifier;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChangeMainIdentifier
{
    public function __construct(public readonly string $mainIdentifierCode)
    {
    }
}
