<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Step\ItemStep;
use League\Flysystem\Filesystem;
use Pim\Bundle\BaseConnectorBundle\Writer\File\FileWriter;
use Pim\Component\Connector\Writer\File\AbstractFileWriter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Archive files written by job execution to provide them through a download button
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileWriterArchiver extends AbstractFilesystemArchiver
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
            $writer = $step->getWriter();

            if ($this->isUsableWriter($writer)) {
                $key = strtr(
                    $this->getRelativeArchivePath($jobExecution),
                    [
                        '%filename%' => basename($writer->getPath()),
                    ]
                );
                // TODO: fix this to access to file path from the job execution parameters
                $this->filesystem->put($key, file_get_contents($writer->getPath()));
            }
        }
    }

    /**
     * Verify if the writer is usable or not
     *
     * @param ItemWriterInterface $writer
     *
     * @return bool
     */
    protected function isUsableWriter(ItemWriterInterface $writer)
    {
        $isDeprecatedWriter = ($writer instanceof FileWriter);
        $isNewWriter = ($writer instanceof AbstractFileWriter);

        return ($isDeprecatedWriter || $isNewWriter) && is_file($writer->getPath());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'output';
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobExecution $jobExecution)
    {
        $job = $this->getConnectorRegistry()->getJob($jobExecution->getJobInstance());
        foreach ($job->getSteps() as $step) {
            if ($step instanceof ItemStep && $this->isUsableWriter($step->getWriter())) {
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
