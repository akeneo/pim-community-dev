<?php

namespace Pim\Component\Connector\Reader\File\Product;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
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

    /**
     * @return array
     */
    protected function getArrayConverterOptions()
    {
        return [
            'mapping'           => $this->getMapping(),
            'default_values'    => $this->getDefaultValues(),
            'with_associations' => false
        ];
    }

    /**
     * @return array
     */
    protected function getMapping()
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        return [
            $jobParameters->get('familyColumn')     => 'family',
            $jobParameters->get('categoriesColumn') => 'categories',
            $jobParameters->get('groupsColumn')     => 'groups'
        ];
    }

    /**
     * @return array
     */
    protected function getDefaultValues()
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        return ['enabled' => $jobParameters->get('enabled')];
    }
}
