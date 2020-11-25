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

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Reader\File\Csv;

use Akeneo\ReferenceEntity\Infrastructure\Connector\ArrayConverter\FlatToStandard\Record;
use Akeneo\Tool\Component\Connector\Reader\File\Csv\Reader;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class RecordReader extends Reader
{
    protected function getArrayConverterOptions(): array
    {
        return [Record::DIRECTORY_PATH_OPTION_KEY => $this->fileIterator->getDirectoryPath()];
    }
}
