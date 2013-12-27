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
class InvalidItemsCsvArchiver extends AbstractArchiver
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

        $this->filesystem->write(
            strtr(
                $this->getRelativeArchivePath($jobExecution),
                array('%filename%' => 'invalid_items.csv')
            ),
            $content,
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getArchives(JobExecution $jobExecution)
    {
        $archives = array();
        $keys = $this->filesystem->listKeys(dirname($this->getRelativeArchivePath($jobExecution)));
        foreach ($keys['keys'] as $key) {
            $archives[] = $this->filesystem->createStream($key);
        }

        return $archives;
    }

    /**
     * Set the header row
     *
     * @param array $header
     */
    public function setHeader(array $header)
    {
        $this->header = $header;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'invalid';
    }
}
