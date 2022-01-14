<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Application\Command;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 */
class UpdatePlatformFamilyCommand
{
    public string $code;
    public string $platformCode;
    public string $label;
    public array $requirements;
}
