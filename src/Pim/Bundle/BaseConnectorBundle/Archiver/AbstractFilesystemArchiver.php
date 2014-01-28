<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Oro\Bundle\BatchBundle\Entity\JobExecution;

/**
 * Base archiver
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFilesystemArchiver implements ArchiverInterface
{
    /** @var \Gaufrette\Filesystem */
    protected $filesystem;

    /**
     * {@inheritdoc}
     */
    public function getArchives(JobExecution $jobExecution)
    {
        $archives = array();
        $keys = $this->filesystem->listKeys(dirname($this->getRelativeArchivePath($jobExecution)));
        foreach ($keys['keys'] as $key) {
            $archives[basename($key)] = $key;
        }

        return $archives;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchive(JobExecution $jobExecution, $key)
    {
        $archives = $this->getArchives($jobExecution);

        if (!isset($archives[$key])) {
            throw new \InvalidArgumentException(
                sprintf('Key "%s" does not exist', $key)
            );
        }

        return $this->filesystem->createStream($archives[$key]);
    }

    /**
     * Get the relative archive path in the file system
     *
     * @param JobExecution $jobExecution
     *
     * @return string
     */
    protected function getRelativeArchivePath(JobExecution $jobExecution)
    {
        $jobInstance = $jobExecution->getJobInstance();

        return sprintf(
            '%s/%s/%s/%s/%%filename%%',
            $jobInstance->getType(),
            $jobInstance->getAlias(),
            $jobExecution->getId(),
            $this->getName()
        );
    }
}
