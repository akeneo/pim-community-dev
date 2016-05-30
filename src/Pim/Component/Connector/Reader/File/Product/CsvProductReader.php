<?php

namespace Pim\Component\Connector\Reader\File\Product;

use Pim\Component\Connector\ArchiveStorage;
use Pim\Component\Connector\Reader\File\CsvReader;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;

/**
 * Product csv reader
 *
 * This specialized csv reader exists to replace relative media path to absolute path, in order for later process to
 * know where to find the files.
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvProductReader extends CsvReader
{
    /** @var MediaPathTransformer */
    protected $mediaPathTransformer;

    /**
     * @param FileIteratorFactory  $fileIteratorFactory,
     * @param ArchiveStorage       $archiveStorage
     * @param MediaPathTransformer $mediaPathTransformer
     */
    public function __construct(
        FileIteratorFactory $fileIteratorFactory,
        ArchiveStorage $archiveStorage,
        MediaPathTransformer $mediaPathTransformer
    ) {
        parent::__construct($fileIteratorFactory, $archiveStorage);

        $this->mediaPathTransformer = $mediaPathTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $data = parent::read();

        if (!is_array($data)) {
            return $data;
        }

        return $this->mediaPathTransformer->transform($data, $this->fileIterator->getDirectoryPath());
    }
}
