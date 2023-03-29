<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CodesSplitter
{
    public static function split(string $codesList): array
    {
        $codesWithQuotes = \preg_split('/(, )|( and )/', $codesList);

        return \array_map(
            static fn (string $codeWithQuotes): string => \substr($codeWithQuotes, 1, -1),
            $codesWithQuotes
        );
    }
}
