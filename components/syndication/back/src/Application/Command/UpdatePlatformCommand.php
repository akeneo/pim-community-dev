<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Application\Command;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 */
class UpdatePlatformCommand
{
    public string $code;
    public string $label;
    public bool $enabled;
    public array $families;
}
