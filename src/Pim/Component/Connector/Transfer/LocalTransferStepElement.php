<?php

namespace Pim\Component\Connector\Transfer;

use Akeneo\Component\Batch\Model\StepExecution;
use Pim\Component\Connector\ArchiveDirectory;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Step element able to copy files on the local filesystem.
 * For instance,
 *      TODO: update the example with multiple files
 *    - after an export, copy /home/akeneo/pim/app/archives/export/csv_family_export/14/output/output.csv to /tmp/family.csv
 *    - before an import, copy /tmp/family.csv to /home/akeneo/pim/app/archives/import/csv_family_import/15/input/input.csv
 *
 * The number of transferred files is updated in the key "transferred_files" of the step execution summary.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalTransferStepElement implements TransferStepElementInterface
{
    /** @var ArchiveDirectory */
    protected $archiveDirectory;

    /** @var bool */
    protected $isFromArchiveToLocal;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param ArchiveDirectory $archiveDirectory
     * @param bool             $fromArchiveToLocal
     */
    public function __construct(ArchiveDirectory $archiveDirectory, $fromArchiveToLocal)
    {
        $this->archiveDirectory     = $archiveDirectory;
        $this->isFromArchiveToLocal = $fromArchiveToLocal;
    }

    /**
     * {@inheritdoc}
     */
    public function transfer()
    {
        $filePath = $this->stepExecution->getJobParameters()->get('filePath');
        $archiveFilePath = $this->getArchiveFilePath();

        if ($this->isFromArchiveToLocal) {
            $source = $archiveFilePath;
            $dest = $filePath;
        } else {
            $source = $filePath;
            $dest = $archiveFilePath;
        }

        $filesystem = new Filesystem();

        try {
            $filesystem->copy($source, $dest);
        } catch (\Exception $e) {
            throw new TransferException(
                sprintf('Impossible to transfer locally "%s" to "%s".', $source, $dest),
                $e->getCode(),
                $e
            );
        }

        $this->stepExecution->incrementSummaryInfo('transferred_files');
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @return string
     */
    protected function getArchiveFilePath()
    {
        $filename = basename($this->stepExecution->getJobParameters()->get('filePath'));
        $archiveDirectory = $this->archiveDirectory->getAbsolute($this->stepExecution->getJobExecution());

        return $archiveDirectory . DIRECTORY_SEPARATOR . $filename;
    }
}
