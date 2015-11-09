<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

use Akeneo\Bundle\BatchBundle\Job\RuntimeErrorException;

/**
 * Write product data into a csv file on the filesystem
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvProductWriter extends CsvWriter
{
    /** @var string */
    protected $bufferFile;

    /** @var array */
    protected $headers = [];

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        if (!is_dir(dirname($this->getPath()))) {
            mkdir(dirname($this->getPath()), 0777, true);
        }

        foreach ($items as $item) {
            $this->writeProductToBuffer($item['product']);

            foreach ($item['media'] as $media) {
                if ($media && isset($media['filePath']) && $media['filePath']) {
                    $this->copyMedia($media);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * Override of CsvWriter flush method to use the file buffer
     */
    public function flush()
    {
        if (!is_file($this->bufferFile)) {
            return;
        }

        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            mkdir(dirname($exportDirectory), 0777, true);
        }

        $this->writtenFiles[$this->getPath()] = basename($this->getPath());

        if (false === $csvFile = fopen($this->getPath(), 'w')) {
            throw new RuntimeErrorException('Failed to open file %path%', ['%path%' => $this->getPath()]);
        }

        $header = $this->isWithHeader() ? $this->headers : [];
        if (false === fputcsv($csvFile, $header, $this->delimiter)) {
            throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
        }

        $bufferHandle = fopen($this->bufferFile, 'r');
        $hollowProduct = array_fill_keys($this->headers, '');
        while (null !== $bufferedProduct = $this->readProductFromBuffer($bufferHandle)) {
            $fullProduct = array_replace($hollowProduct, $bufferedProduct);
            if (false === fputcsv($csvFile, $fullProduct, $this->delimiter, $this->enclosure)) {
                throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
            } elseif (null !== $this->stepExecution) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }

        fclose($bufferHandle);
        unlink($this->bufferFile);
        fclose($csvFile);
    }

    /**
     * @param array $media
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

    /**
     * Write the product data to the file buffer, and collect the headers
     *
     * @param array $product
     */
    protected function writeProductToBuffer(array $product)
    {
        if (!is_file($this->bufferFile)) {
            $this->bufferFile = tempnam(sys_get_temp_dir(), 'pim_products_buffer_');
        }

        file_put_contents($this->bufferFile, json_encode($product) . "\n", FILE_APPEND);

        $this->headers = $this->getAllKeys([
            array_flip($this->headers),
            $product
        ]);
    }

    /**
     * Read the next line from the products buffer
     *
     * @param resource $bufferHandle
     *
     * @return array|null
     */
    protected function readProductFromBuffer($bufferHandle)
    {
        $rawLine = fgets($bufferHandle);

        return false !== $rawLine ? json_decode($rawLine, true) : null;
    }
}
