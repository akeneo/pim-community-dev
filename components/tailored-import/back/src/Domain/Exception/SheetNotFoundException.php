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

namespace Akeneo\Platform\TailoredImport\Domain\Exception;

class SheetNotFoundException extends \RuntimeException
{
    public function __construct(string $expectedSheet)
    {
        $message = sprintf('The sheet named "%s" was not found', $expectedSheet);
        parent::__construct($message);
    }
}
