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

class MismatchedFileHeadersException extends \Exception
{
    public function __construct(array $expectedHeader, array $actualHeader)
    {
        $message = sprintf(
            "Header label does not match job configuration.\nExpected: %s\nActual: %s",
            implode(', ', $expectedHeader),
            implode(', ', $actualHeader),
        );
        parent::__construct($message);
    }
}
