<?php

namespace Pim\Component\Connector\Transfer;

use Akeneo\Component\Batch\Model\StepExecution;
use Pim\Component\Connector\ArchiveStorage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Step element able to copy output files from the archives to the desired local path.
 *
 * For instance, after an export, copy
 *    /home/akeneo/pim/app/archives/export/csv_family_export/14/output/csv_family_export to /tmp/family.csv
 * or copy
 *    /home/akeneo/pim/app/archives/export/csv_family_export/14/output/xlsx_family_export_1 to /tmp/family_1.xlsx
 *    /home/akeneo/pim/app/archives/export/csv_family_export/14/output/xlsx_family_export_2 to /tmp/family_2.xlsx
 *    /home/akeneo/pim/app/archives/export/csv_family_export/14/output/xlsx_family_export_3 to /tmp/family_3.xlsx
 *
 * The number of transferred files is updated in the key "transferred_files" of the step execution summary.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArchiveToLocalTransferStepElement implements TransferStepElementInterface
{
    /** @var ArchiveStorage */
    protected $archiveStorage;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param ArchiveStorage $archiveStorage
     */
    public function __construct(ArchiveStorage $archiveStorage)
    {
        $this->archiveStorage = $archiveStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function transfer()
    {
        $filesystem = new Filesystem();

        foreach ($this->listArchivedFiles() as $fileInfo) {
            $source = $fileInfo->getPathname();
            $dest = $this->archivePathnameToLocalPathname($source);

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

    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalFilename()
    {
        return basename($this->stepExecution->getJobParameters()->get('filePath'));
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @return Finder
     *
     * @throws \Exception
     */
    protected function listArchivedFiles()
    {
        $archiveStorage = $this->archiveStorage->getAbsoluteDirectory($this->stepExecution->getJobExecution());

        $finder = new Finder();
        $finder->in($archiveStorage)->files()->depth('== 0');

        return $finder;
    }

    /**
     * All
     *
     * @param string $archivePathname
     *
     * @return string
     */
    private function archivePathnameToLocalPathname($archivePathname)
    {
        $archiveFileInfo = new \SplFileInfo($archivePathname);
        $archiveFilename = $archiveFileInfo->getFilename();
        $jobCode = $this->stepExecution->getJobExecution()->getJobInstance()->getCode();

        // we can't use \SplFileInfo on $localDefaultPathname as the file does not exist yet
        // (and maybe it will never exists in case the output has been split into multiple files)
        $localDefaultPathname = $this->stepExecution->getJobParameters()->get('filePath');
        $localDirectory = dirname($localDefaultPathname);
        $localFileInfo = pathinfo($localDefaultPathname);
        $localFilename =
            str_replace($jobCode, $localFileInfo['filename'], $archiveFilename) . '.' . $localFileInfo['extension'];

        return $localDirectory . DIRECTORY_SEPARATOR . $localFilename;
    }
}
