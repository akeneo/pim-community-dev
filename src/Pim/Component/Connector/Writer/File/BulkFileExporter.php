<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Copy every media to the specific target during an export
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BulkFileExporter
{
    /** @var FileExporterInterface */
    protected $fileExporter;

    /** @var array */
    protected $errors;

    public function __construct(FileExporterInterface $fileExporter)
    {
        $this->errors = [];
        $this->fileExporter = $fileExporter;
    }

    /**
     * Export the media of the items to the target
     *
     * @param array  $items
     * @param string $target
     */
    public function exportAll(array $items, $target)
    {
        foreach ($items as $media) {
            foreach ($media as $medium) {
                $this->doCopy($medium, $target);
            }
        }
    }

    /**
     * Get an array of errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Copy a medium to the target
     *
     * @param array|mixed $medium
     * @param string      $target
     *
     * @throws IOException
     * @throws \LogicException
     */
    protected function doCopy($medium, $target)
    {
        if (isset($medium['filePath']) && isset($medium['exportPath'])) {
            $target = $target.DIRECTORY_SEPARATOR.$medium['exportPath'];
            $fileSystem = new Filesystem();
            $fileSystem->mkdir(dirname($target));

            try {
                $this->fileExporter->export($medium['filePath'], $target, $medium['storageAlias']);
            } catch (FileTransferException $e) {
                $this->addError('The media has not been found or is not currently available', $medium);
            } catch (\LogicException $e) {
                $this->addError(sprintf('The media has not been copied. %s', $e->getMessage()), $medium);
            }
        }
    }

    /**
     * @param string $message
     * @param string $medium
     */
    protected function addError($message, $medium)
    {
        $this->errors[] = [
            'message' => $message,
            'medium'  => $medium,
        ];
    }
}
