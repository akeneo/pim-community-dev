<?php

namespace Pim\Bundle\ImportExportBundle\Archiver;

use Gaufrette\Filesystem;
use Oro\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\ImportExportBundle\EventListener\InvalidItemsCollector;
use Pim\Bundle\ImportExportBundle\Encoder\CsvEncoder;

/**
 * Archiver of invalid items into a csv file
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidItemsCsvArchiver implements ArchiverInterface
{
    /** @var InvalidItemsCollector */
    protected $collector;

    /** @var CsvEncoder */
    protected $encoder;

    /** @var Filesystem */
    protected $filesystem;

    /** @var string */
    protected $header = '';

    /**
     * @param InvalidItemsCollector $collector
     */
    public function __construct(InvalidItemsCollector $collector, CsvEncoder $encoder, Filesystem $filesystem)
    {
        $this->collector  = $collector;
        $this->encoder    = $encoder;
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
        $content = $this->encoder->encode($this->header, 'csv');

        foreach ($this->collector->getInvalidItems() as $item) {
            $content .= $this->encoder->encode($item, 'csv');
        }

        $this->filesystem->write($this->getRelativeArchivePath($jobExecution), $content, true);
    }

    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * Get the relative archive path in the file system
     *
     * @return string
     */
    protected function getRelativeArchivePath(JobExecution $jobExecution)
    {
        $jobInstance = $jobExecution->getJobInstance();

        return sprintf(
            '%s/%s/%s/invalid_items.csv',
            $jobInstance->getType(),
            $jobInstance->getAlias(),
            $jobInstance->getId()
        );
    }
}
