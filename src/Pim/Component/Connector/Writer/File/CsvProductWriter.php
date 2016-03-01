<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Buffer\BufferFactory;
use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Pim\Component\Connector\Writer\File\Product\FlatRowBuffer;
use Pim\Component\Connector\Writer\File\Product\MediaCopier;

/**
 * Write product data into a csv file on the local filesystem
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvProductWriter extends CsvWriter
{
    /** @var MediaCopier */
    private $mediaCopier;

    public function __construct(
        FilePathResolverInterface $filePathResolver,
        BufferFactory $bufferFactory,
        MediaCopier $mediaCopier
    ) {
        parent::__construct($filePathResolver, $bufferFactory);

        $this->mediaCopier = $mediaCopier;
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

        $this->mediaCopier->copy($items, $exportDirectory);

        foreach ($this->mediaCopier->getErrors() as $error) {
            $this->stepExecution->addWarning(
                $this->getName(),
                $error['message'],
                [],
                $error['medium']
            );
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
}
