<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Reader\File;

use Akeneo\Tool\Component\Connector\Reader\File\Csv\Reader;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvUserReader extends Reader
{
    /**
     * {@inheritdoc}
     */
    public function read(): ?array
    {
        $item = parent::read();
        if (null !== ($item['avatar']['filePath'] ?? null)) {
            $item['avatar']['filePath'] = \sprintf(
                '%s%s%s',
                $this->fileIterator->getDirectoryPath(),
                DIRECTORY_SEPARATOR,
                $item['avatar']['filePath']
            );
        }

        return $item;
    }
}
