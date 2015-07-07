<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

/**
 * Write variant group data into a csv file on the filesystem
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvVariantGroupWriter extends CsvWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $variantGroups = [];

        if (!is_dir(dirname($this->getPath()))) {
            mkdir(dirname($this->getPath()), 0777, true);
        }

        foreach ($items as $item) {
            $variantGroups[] = $item['variant_group'];
            foreach ($item['media'] as $media) {
                if ($media && isset($media['filePath']) && $media['filePath']) {
                    $this->copyMedia($media);
                }
            }
        }

        $this->items = array_merge($this->items, $variantGroups);
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
        if (copy($media['filePath'], $target)) {
            $this->writtenFiles[$target] = $media['exportPath'];
        } else {
            $this->stepExecution->addWarning(
                $this->getName(),
                'The media has not been found or is not currently available',
                [],
                $media
            );
        }
    }
}
