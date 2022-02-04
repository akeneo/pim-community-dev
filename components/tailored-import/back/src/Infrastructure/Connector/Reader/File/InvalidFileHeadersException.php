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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector\Reader\File;

class InvalidFileHeadersException extends \Exception
{
    private const MESSAGE = 'akeneo.tailored_import.jobs.reader.invalid_file_headers';

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
