<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Gaufrette\Filesystem;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Pim\Bundle\BaseConnectorBundle\EventListener\InvalidItemsCollector;
use Pim\Bundle\TransformBundle\Encoder\CsvEncoder;

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

    /** @var CsvEncoder */
    protected $encoder;

    /** @var array */
    protected $header = array();

    /**
     * @param InvalidItemsCollector $collector
     * @param CsvEncoder            $encoder
     * @param Filesystem            $filesystem
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
