<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\Csv;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\File\FindFamilyCodesInterface;
use Akeneo\Tool\Component\Connector\Reader\File\FileIteratorFactory;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindFamilyCodes implements FindFamilyCodesInterface
{
    private FileIteratorFactory $fileIteratorFactory;

    public function __construct(FileIteratorFactory $fileIteratorFactory)
    {
        $this->fileIteratorFactory = $fileIteratorFactory;
    }

    public function execute(string $filePath): \Iterator
    {
        $iterator = $this->fileIteratorFactory->create($filePath);

        foreach ($iterator as $item) {
            yield $item['code'];
        }
    }
}
