<?php

namespace Pim\Component\Connector\Reader\File;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
// TODO Move mediaPathTransformer
use Pim\Component\Connector\Reader\File\Product\MediaPathTransformer;

/**
 * Variant Group csv reader
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvVariantGroupReader extends CsvReader
{
    /** @var MediaPathTransformer */
    protected $mediaPathTransformer;

    /**
     * @param FileIteratorFactory     $fileIteratorFactory,
     * @param ArrayConverterInterface $converter
     * @param MediaPathTransformer    $mediaPathTransformer
     */
    public function __construct(
        FileIteratorFactory $fileIteratorFactory,
        ArrayConverterInterface $converter,
        MediaPathTransformer $mediaPathTransformer
    ) {
        parent::__construct($fileIteratorFactory, $converter);

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
