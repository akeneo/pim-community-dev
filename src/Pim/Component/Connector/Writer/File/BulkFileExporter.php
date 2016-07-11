<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
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

    /** @var array */
    protected $copiedMedia;

    /**
     * @param FileExporterInterface $fileExporter
     */
    public function __construct(FileExporterInterface $fileExporter)
    {
        $this->errors = [];
        $this->copiedMedia = [];
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
     *  [
     *      [
     *          'message' => (string),
     *          'medium'  => [
     *              'filePath'     => (string),
     *              'exportPath'   => (string),
     *              'storageAlias' => (string)
     *          ]
     *      ],
     *      [...]
     *  ]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Returns media that have been well copied and path of the copy
     *
     * @return array
     *  [
     *      [
     *          'copyPath'       => (string),
     *          'originalMedium' => [
     *              'filePath'     => (string),
     *              'exportPath'   => (string),
     *              'storageAlias' => (string)
     *          ]
     *      ],
     *      [...]
     *  ]
     */
    public function getCopiedMedia()
    {
        return $this->copiedMedia;
    }

    /**
     * Copy a medium to the target
     *
     * @param array|mixed $medium
     * @param string      $target
     */
    protected function doCopy($medium, $target)
    {
        if (isset($medium['filePath']) && isset($medium['exportPath'])) {
            $target = $target . DIRECTORY_SEPARATOR . $medium['exportPath'];
            $fileSystem = new Filesystem();
            $fileSystem->mkdir(dirname($target));

            try {
                $this->fileExporter->export($medium['filePath'], $target, $medium['storageAlias']);
                $this->addCopiedMedium($medium, $target);
            } catch (FileTransferException $e) {
                $this->addError($medium, 'The media has not been found or is not currently available');
            } catch (\LogicException $e) {
                $this->addError($medium, sprintf('The media has not been copied. %s', $e->getMessage()));
            }
        }
    }

    /**
     * @param array  $medium
     * @param string $message
     */
    protected function addError(array $medium, $message)
    {
        $this->errors[] = [
            'message' => $message,
            'medium'  => $medium,
        ];
    }

    /**
     * @param array  $medium
     * @param string $copyPath
     */
    protected function addCopiedMedium(array $medium, $copyPath)
    {
        $this->copiedMedia[] = [
            'copyPath'       => $copyPath,
            'originalMedium' => $medium
        ];
    }
}
