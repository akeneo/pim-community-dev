<?php

namespace Pim\Component\Connector\Writer\File\Product;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Pim\Component\Connector\Writer\File\FileExporterInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaCopier
{
    /** @var FileExporterInterface */
    private $fileExporter;
    /** @var array */
    private $errors;

    public function __construct(FileExporterInterface $fileExporter)
    {
        $this->errors = [];
        $this->fileExporter = $fileExporter;
    }

    /**
     * @param array  $items
     * @param string $target
     */
    public function copy(array $items, $target)
    {
        foreach ($items as $item) {
            $this->doCopy($item['media'], $target);
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array  $media
     * @param string $target
     */
    private function doCopy(array $media, $target)
    {
        foreach ($media as $medium) {
            if (isset($medium['filePath']) && isset($medium['exportPath'])) {
                $target = $target.DIRECTORY_SEPARATOR.$medium['exportPath'];
                (new Filesystem())->mkdir($target);

                try {
                    $this->fileExporter->export($medium['filePath'], $target, $medium['storageAlias']);
                } catch (FileTransferException $e) {
                    $this->addError('The media has not been found or is not currently available', $medium);
                } catch (\LogicException $e) {
                    $this->addError(sprintf('The media has not been copied. %s', $e->getMessage()), $medium);
                }
            }
        }
    }

    /**
     * @param string $message
     * @param string $medium
     */
    private function addError($message, $medium)
    {
        $this->errors[] = [
            'message' => $message,
            'medium'  => $medium,
        ];
    }
}
