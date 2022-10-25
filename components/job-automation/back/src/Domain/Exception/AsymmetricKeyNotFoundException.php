<?php

declare(strict_types=1);
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Domain\Exception;

class AsymmetricKeyNotFoundException extends \Exception
{
    public const MESSAGE = 'No keys found for %s';

    public function __construct(string $code)
    {
        parent::__construct(sprintf(self::MESSAGE, $code));
    }
}
