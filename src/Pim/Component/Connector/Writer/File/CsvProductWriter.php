<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Buffer\BufferFactoryInterface;
use Akeneo\Component\FileStorage\Exception\FileTransferException;

/**
 * Write product data into a csv file on the local filesystem
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvProductWriter extends CsvWriter
{
    /** @var FileExporterInterface */
    protected $fileExporter;

    /**
     * @param FilePathResolverInterface $filePathResolver
     * @param BufferFactoryInterface    $bufferFactory
     * @param FileExporterInterface     $fileExporter
     */
    public function __construct(
        FilePathResolverInterface $filePathResolver,
        BufferFactoryInterface $bufferFactory,
        FileExporterInterface $fileExporter
    ) {
        parent::__construct($filePathResolver, $bufferFactory);

        $this->fileExporter = $fileExporter;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $products = [];
        foreach ($items as $item) {
            $products[] = $item['product'];
        }
        parent::write($products);

        $exportDirectory = dirname($this->getPath());
        if (!is_dir($exportDirectory)) {
            $this->localFs->mkdir($exportDirectory);
        }

        foreach ($items as $item) {
            foreach ($item['media'] as $media) {
                if ($media && isset($media['filePath']) && $media['filePath']) {
                    $this->copyMedia($media);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
        parent::setConfiguration($config);

        if (!isset($config['mainContext'])) {
            return;
        }

        foreach ($config['mainContext'] as $key => $value) {
            $this->filePathResolverOptions['parameters']['%' . $key . '%'] = $value;
        }
    }

    /**
     * @param array $media
     */
    protected function copyMedia(array $media)
    {
        $target = dirname($this->getPath()) . DIRECTORY_SEPARATOR . $media['exportPath'];

        if (!is_dir(dirname($target))) {
            $this->localFs->mkdir(dirname($target));
        }

        try {
            $this->fileExporter->export($media['filePath'], $target, $media['storageAlias']);
            $this->writtenFiles[$target] = $media['exportPath'];
        } catch (FileTransferException $e) {
            $this->stepExecution->addWarning(
                $this->getName(),
                'The media has not been found or is not currently available',
                [],
                $media
            );
        } catch (\LogicException $e) {
            $this->stepExecution->addWarning(
                $this->getName(),
                sprintf('The media has not been copied. %s', $e->getMessage()),
                [],
                $media
            );
        }
    }
}
