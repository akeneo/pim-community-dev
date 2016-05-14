<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Step\ItemStep;
use League\Flysystem\Filesystem;
use Pim\Bundle\BaseConnectorBundle\Reader\File\FileReader;
use Pim\Component\Connector\Reader\File\CsvReader;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Archive files read by job execution to provide them through a download button
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileReaderArchiver extends AbstractFilesystemArchiver
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem, ContainerInterface $container)
    {
        $this->filesystem = $filesystem;
        $this->container = $container;
    }

    /**
     * Archive files used by job execution (input / output)
     *
     * @param JobExecution $jobExecution
     */
    public function archive(JobExecution $jobExecution)
    {
        $job = $this->getConnectorRegistry()->getJob($jobExecution->getJobInstance());
        foreach ($job->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }
            $reader = $step->getReader();

            if ($this->isReaderUsable($reader)) {
                $jobParameters = $jobExecution->getJobParameters();
                $filePath = $jobParameters->get('filePath');
                $key = strtr(
                    $this->getRelativeArchivePath($jobExecution),
                    [
                        '%filename%' => basename($filePath),
                    ]
                );
                $this->filesystem->put($key, file_get_contents($filePath));
            }
        }
    }

    /**
     * Verify if the reader is usable or not
     *
     * @param ItemReaderInterface $reader
     *
     * @return bool
     */
    protected function isReaderUsable(ItemReaderInterface $reader)
    {
        return $reader instanceof FileReader || $reader instanceof CsvReader;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'input';
    }

    /**
     * Check if the job execution is supported
     *
     * @param JobExecution $jobExecution
     *
     * @return bool
     */
    public function supports(JobExecution $jobExecution)
    {
        $job = $this->getConnectorRegistry()->getJob($jobExecution->getJobInstance());
        foreach ($job->getSteps() as $step) {
            if ($step instanceof ItemStep && $this->isReaderUsable($step->getReader())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Should be changed with TIP-418, here we work around a circular reference due to the way we instanciate the whole
     * Job classes in the DIC
     *
     * @return ConnectorRegistry
     */
    protected final function getConnectorRegistry()
    {
        return $this->container->get('akeneo_batch.connectors');
    }
}
