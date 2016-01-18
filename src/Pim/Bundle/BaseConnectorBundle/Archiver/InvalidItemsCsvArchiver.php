<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use League\Flysystem\Filesystem;
use Pim\Bundle\BaseConnectorBundle\EventListener\InvalidItemsCollector;
use Pim\Bundle\BaseConnectorBundle\Writer\File\CsvWriter;

/**
 * Archiver of invalid items into a csv file
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidItemsCsvArchiver extends AbstractFilesystemArchiver
{
    /** @var InvalidItemsCollector */
    protected $collector;

    /** @var CsvWriter */
    protected $writer;

    /**
     * Constructor
     *
     * @param InvalidItemsCollector $collector
     * @param CsvWriter             $writer
     * @param Filesystem            $filesystem
     */
    public function __construct(
        InvalidItemsCollector $collector,
        CsvWriter $writer,
        Filesystem $filesystem
    ) {
        $this->collector  = $collector;
        $this->writer     = $writer;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function archive(JobExecution $jobExecution)
    {
        if (!$this->collector->getInvalidItems()) {
            return;
        }
        $key =  strtr(
            $this->getRelativeArchivePath($jobExecution),
            array('%filename%' => 'invalid_items.csv')
        );
        $this->filesystem->put($key, '');
        $this->writer->setFilePath(
            $this->filesystem->getAdapter()->getPathPrefix() .
            $key
        );
        $this->writer->initialize();
        $this->writer->write($this->collector->getInvalidItems());
        $this->writer->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'invalid';
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobExecution $jobExecution)
    {
        if ($this->collector->getInvalidItems()) {
            foreach ($this->collector->getInvalidItems() as $elements) {
                foreach ($elements as $element) {
                    if (is_array($element)) {
                        return false;
                    }
                }
            }

            return true;
        }

        return false;
    }
}
