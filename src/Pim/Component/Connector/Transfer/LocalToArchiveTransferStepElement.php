<?php

namespace Pim\Component\Connector\Transfer;

use Akeneo\Component\Batch\Model\StepExecution;
use Pim\Component\Connector\ArchiveDirectory;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Step element able to copy output files from input local path to the archives directory.
 *
 * For instance, before an import, copy
 *    /tmp/family.csv to /home/akeneo/pim/app/archives/import/csv_family_import/14/input/csv_family_import
 *
 * The number of transferred files is updated in the key "transferred_files" of the step execution summary.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalToArchiveTransferStepElement implements TransferStepElementInterface
{
    /** @var ArchiveDirectory */
    protected $archiveDirectory;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param ArchiveDirectory $archiveDirectory
     */
    public function __construct(ArchiveDirectory $archiveDirectory)
    {
        $this->archiveDirectory = $archiveDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function transfer()
    {
        $archiveDirectory = $this->archiveDirectory->getAbsolute($this->stepExecution->getJobExecution());
        $archiveFilename = $this->stepExecution->getJobExecution()->getJobInstance()->getCode();

        $source = $this->stepExecution->getJobParameters()->get('filePath');
        $dest = $archiveDirectory . $archiveFilename;

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
}
