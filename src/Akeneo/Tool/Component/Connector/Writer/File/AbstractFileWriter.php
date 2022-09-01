<?php

namespace Akeneo\Tool\Component\Connector\Writer\File;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\LocalStorage;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Abstract file writer to handle file naming and configuration-related logic.
 * write() method must be implemented by children.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFileWriter implements ItemWriterInterface, StepExecutionAwareInterface, ArchivableWriterInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var Filesystem */
    protected $localFs;

    /** @var string DateTime format for the file path placeholder */
    protected $datetimeFormat = 'Y-m-d_H-i-s';

    protected array $writtenFiles = [];

    public function __construct()
    {
        $this->localFs = new Filesystem();
    }

    /**
     * Get the file path in which to write the data
     *
     * @param array $placeholders
     *
     * @return string
     */
    public function getPath(array $placeholders = []): string
    {
        $parameters = $this->stepExecution->getJobParameters();
        $jobExecution = $this->stepExecution->getJobExecution();
        $storage = $parameters->get('storage');
        $filePath = LocalStorage::TYPE === $storage['type']
            ? $storage['file_path']
            : sprintf('%s%s%s', sys_get_temp_dir(), DIRECTORY_SEPARATOR, $storage['file_path']);

        if (false !== strpos($filePath, '%')) {
            $datetime = $this->stepExecution->getStartTime()->format($this->datetimeFormat);
            $defaultPlaceholders = ['%datetime%' => $datetime, '%job_label%' => ''];

            if (isset($placeholders['%job_label%'])) {
                $placeholders['%job_label%'] = $this->sanitize($placeholders['%job_label%']);
            } elseif (null !== $jobExecution->getJobInstance()) {
                $defaultPlaceholders['%job_label%'] = $this->sanitize($jobExecution->getJobInstance()->getLabel());
            }
            $replacePairs = array_merge($defaultPlaceholders, $placeholders);
            $filePath = strtr($filePath, $replacePairs);
        }

        return $filePath;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getWrittenFiles(): array
    {
        return $this->writtenFiles;
    }

    /**
     * Replace [^A-Za-z0-9\.] from a string by '_'
     *
     * @param string $value
     *
     * @return string
     */
    protected function sanitize($value)
    {
        return preg_replace('#[^A-Za-z0-9\.]#', '_', $value);
    }
}
