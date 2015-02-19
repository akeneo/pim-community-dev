<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

/**
 * Write product data into a csv file on the filesystem
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvProductWriter extends CsvWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $products = [];

        if (!is_dir(dirname($this->getPath()))) {
            mkdir(dirname($this->getPath()), 0777, true);
        }

        foreach ($items as $item) {
            $products[] = $item['product'];
            foreach ($item['media'] as $media) {
                if ($media && isset($media['filePath']) && $media['filePath']) {
                    $this->copyMedia($media);
                }
            }
        }

        $this->items = array_merge($this->items, $products);
    }

    /**
     * @param array $media
     *
     * @return void
     */
    protected function copyMedia(array $media)
    {
        $target = sprintf('%s/%s', dirname($this->getPath()), $media['exportPath']);

        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }
        if (is_file($media['filePath'])) {
            $this->copyFile($media, $target);
        } else {
            $this->stepExecution->addWarning(
                $this->getName(),
                'The media has not been found or is not currently available',
                [],
                $media
            );
        }
    }

    /**
     * @param array  $media
     * @param string $target
     */
    protected function copyFile(array $media, $target)
    {
        if (copy($media['filePath'], $target)) {
            $this->writtenFiles[$target] = $media['exportPath'];
        } else {
            $this->stepExecution->addWarning(
                $this->getName(),
                'The media has not been copied',
                [],
                $media
            );
        }
    }
}
