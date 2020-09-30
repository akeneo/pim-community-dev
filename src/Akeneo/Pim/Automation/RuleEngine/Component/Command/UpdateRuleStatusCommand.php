<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Command;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class UpdateRuleStatusCommand
{
    private $code;
    private $enabled;

    public function __construct(string $code, bool $enabled)
    {
        $this->code = $code;
        $this->enabled = $enabled;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
