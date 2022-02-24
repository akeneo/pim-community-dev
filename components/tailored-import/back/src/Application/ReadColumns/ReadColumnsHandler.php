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

namespace Akeneo\Platform\TailoredImport\Application\ReadColumns;

use Akeneo\Platform\TailoredImport\Domain\Model\Column;
use Akeneo\Platform\TailoredImport\Domain\Model\ColumnCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\File\FileHeader;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\ReadFileHeadersInterface;

class ReadColumnsHandler
{
    public function __construct(
        private ReadFileHeadersInterface $readFileHeaders,
        private UuidGeneratorInterface $uuidGenerator,
    ) {
    }

    public function handle(ReadColumnsQuery $query): ColumnCollection
    {
        $fileHeaders = $this->readFileHeaders->read($query->getFileKey(), $query->getFileStructure());

        return ColumnCollection::create(array_map(
            fn (FileHeader $fileHeader) => $this->fileHeaderToColumn($fileHeader),
            iterator_to_array($fileHeaders),
        ));
    }

    private function fileHeaderToColumn(FileHeader $fileHeader): Column
    {
        return Column::create(
            $this->uuidGenerator->generate(),
            $fileHeader->getIndex(),
            $fileHeader->getLabel(),
        );
    }
}
