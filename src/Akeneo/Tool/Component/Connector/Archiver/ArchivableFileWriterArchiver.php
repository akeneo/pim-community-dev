<?php

namespace Akeneo\Tool\Component\Connector\Archiver;

use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\Tool\Component\Connector\Writer\File\ArchivableWriterInterface;
use League\Flysystem\Filesystem;

/**
 * Archive job execution files into conventional directories
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArchivableFileWriterArchiver extends AbstractFilesystemArchiver
{
    /** @var ZipFilesystemFactory */
    protected $factory;

    /** @var string */
    protected $directory;

    /** @var Filesystem */
    private $tmpStorageFilesystem;

    /** @var JobRegistry */
    private $jobRegistry;

    /**
     * @param ZipFilesystemFactory $factory
     * @param Filesystem           $filesystem      This is the "archivist" filesystem
     * @param Filesystem           $tmpStorageFilesystem
     * @param JobRegistry          $jobRegistry
     */
    public function __construct(
        ZipFilesystemFactory $factory,
        Filesystem $filesystem,
        Filesystem $tmpStorageFilesystem,
        JobRegistry $jobRegistry
    ) {
        $this->factory = $factory;
        $this->filesystem = $filesystem;
        $this->tmpStorageFilesystem = $tmpStorageFilesystem;
        $this->jobRegistry = $jobRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function archive(JobExecution $jobExecution)
    {
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        foreach ($job->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }
            $writer = $step->getWriter();
            if ($this->isWriterUsable($writer)) {
                $zipName = sprintf(
                    '%s.zip',
                    pathinfo($writer->getPath(), PATHINFO_FILENAME)
                );
                $zipPath = $this->prepareZipPath($jobExecution, $zipName);

                $zipFilesystem = $this->factory->createZip($this->tmpStorageFilesystem, $zipName);

                foreach ($writer->getWrittenFiles() as $fullPath => $localPath) {
                    $zipFilesystem->putStream($localPath, fopen($fullPath, 'r'));
                }

                $tmpArchivePath = $zipFilesystem->getAdapter()->getArchive()->filename;
                $zipFilesystem->getAdapter()->getArchive()->close();

                $this->filesystem->put($zipPath, file_get_contents($tmpArchivePath));
                $this->tmpStorageFilesystem->delete(str_replace(
                    $this->tmpStorageFilesystem->getAdapter()->getPathPrefix(),
                    '',
                    $tmpArchivePath
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'archive';
    }

    /**
     * Get a fresh zip filesystem for the given job execution
     *
     * @param JobExecution $jobExecution
     * @param string       $zipName
     *
     * @return string
     */
    protected function prepareZipPath(JobExecution $jobExecution, $zipName)
    {
        $zipPath = strtr(
            $this->getRelativeArchivePath($jobExecution),
            ['%filename%' => $zipName]
        );

        if (!$this->filesystem->has(dirname($zipPath))) {
            $this->filesystem->createDir(dirname($zipPath));
        }

        return $zipPath;
    }

    /**
     * Verify if the writer is usable or not
     *
     * @param ItemWriterInterface $writer
     *
     * @return bool
     */
    protected function isWriterUsable(ItemWriterInterface $writer)
    {
        return $writer instanceof ArchivableWriterInterface && count($writer->getWrittenFiles()) > 1;
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
        $job = $this->jobRegistry->get($jobExecution->getJobInstance()->getJobName());
        foreach ($job->getSteps() as $step) {
            if ($step instanceof ItemStep && $this->isWriterUsable($step->getWriter())) {
                return true;
            }
        }

        return false;
    }
}
