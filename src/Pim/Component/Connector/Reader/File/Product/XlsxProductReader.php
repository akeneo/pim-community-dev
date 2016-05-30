<?php

namespace Pim\Component\Connector\Reader\File\Product;

use Pim\Component\Connector\ArchiveStorage;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\XlsxReader;

/**
 * Product XLSX reader
 *
 * This specialized XLSX reader exists to replace relative media path to absolute path, in order for later process to
 * know where to find the files.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XlsxProductReader extends XlsxReader
{
    /** @var MediaPathTransformer */
    protected $mediaPathTransformer;

    /** @var ArchiveStorage */
    protected $archiveStorage;

    /**
     * @param FileIteratorFactory  $fileIteratorFactory
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
