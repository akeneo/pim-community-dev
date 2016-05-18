<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use League\Flysystem\Filesystem;
use Pim\Bundle\BaseConnectorBundle\EventListener\InvalidItemsCollector;
use Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\ProductCsvExport;
use Pim\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleCsvExport;
use Pim\Component\Connector\Writer\File\CsvWriter;

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
            ['%filename%' => 'invalid_items.csv']
        );
        $this->filesystem->put($key, '');

        // TODO Archiver will be re-worked within PIM-5094, this part will become useless
        $jobExecution = new JobExecution();
        $provider = new ProductCsvExport(new SimpleCsvExport([]), []);
        $params = $provider->getDefaultValues();
        $params ['filePath'] = $this->filesystem->getAdapter()->getPathPrefix() . $key;
        $jobParameters = new JobParameters($params);
        $jobExecution->setJobParameters($jobParameters);
        $stepExecution = new StepExecution('processor', $jobExecution);
        $this->writer->setStepExecution($stepExecution);

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
