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

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Connector\Reader\File\Csv;

use Akeneo\Pim\Enrichment\AssetManager\Component\Connector\ArrayConverter\FlatToStandard\Asset;
use Akeneo\Tool\Component\Connector\Reader\File\Csv\Reader;

final class AssetReader extends Reader
{
    protected function getArrayConverterOptions(): array
    {
        return [Asset::DIRECTORY_PATH_OPTION_KEY => $this->fileIterator->getDirectoryPath()];
    }
}
