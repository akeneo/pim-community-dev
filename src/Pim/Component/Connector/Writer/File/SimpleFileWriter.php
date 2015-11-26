<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Bundle\BatchBundle\Job\RuntimeErrorException;

/**
 * Write data into a file on the filesystem
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SimpleFileWriter extends AbstractFileWriter
{
    /** @var resource */
    protected $handler;

    /**
     * {@inheritdoc}
     */
    public function write(array $data)
    {
        if (!$this->handler) {
            $path = $this->getPath();
            if (!is_dir(dirname($path))) {
                $this->localFs->mkdir(dirname($path));
            }
            $this->handler = fopen($path, 'w');
        }

        foreach ($data as $entry) {
            if (false === fwrite($this->handler, $entry)) {
                throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
            } else {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }
    }

    /**
     * Close handler when destructing the current instance
     */
    public function __destruct()
    {
        if (is_resource($this->handler)) {
            fclose($this->handler);
        }
    }
}
