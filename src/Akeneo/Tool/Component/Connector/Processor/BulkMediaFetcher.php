<?php

namespace Akeneo\Tool\Component\Connector\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValueInterface;
use Akeneo\Tool\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

/**
 * Fetch every media to the specific target during an export
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BulkMediaFetcher
{
    /** @var FileFetcherInterface */
    protected $mediaFetcher;

    /** @var FileExporterPathGeneratorInterface */
    protected $fileExporterPath;

    /** @var FilesystemProvider */
    protected $filesystemProvider;

    /** @var array */
    protected $errors;

    /**
     * @param FileFetcherInterface               $mediaFetcher
     * @param FilesystemProvider                 $filesystemProvider
     * @param FileExporterPathGeneratorInterface $fileExporterPath
     */
    public function __construct(
        FileFetcherInterface $mediaFetcher,
        FilesystemProvider $filesystemProvider,
        FileExporterPathGeneratorInterface $fileExporterPath
    ) {
        $this->errors = [];
        $this->mediaFetcher = $mediaFetcher;
        $this->fileExporterPath = $fileExporterPath;
        $this->filesystemProvider = $filesystemProvider;
    }

    /**
     * Fetch the media of the items to the target
     *
     * @param WriteValueCollection $values
     * @param string                   $target
     * @param string                   $identifier
     */
    public function fetchAll(WriteValueCollection $values, $target, $identifier)
    {
        $target = DIRECTORY_SEPARATOR !== substr($target, -1) ? $target . DIRECTORY_SEPARATOR : $target;

        foreach ($values as $value) {
            if ($value instanceof MediaValueInterface && null !== $media = $value->getData()) {
                $exportPath = $this->fileExporterPath->generate(
                    [
                        'locale' => $value->getLocaleCode(),
                        'scope'  => $value->getScopeCode()
                    ],
                    [
                        'identifier' => $identifier,
                        'code'       => $value->getAttributeCode()
                    ]
                );

                $this->fetch([
                    'from'    => $media->getKey(),
                    'to'      => [
                        'filePath' => $target . $exportPath,
                        'filename' => $media->getOriginalFilename()
                    ],
                    'storage' => $media->getStorage()
                ]);
            }
        }
    }

    /**
     * Get an array of errors
     *
     * @return array
     *  [
     *      [
     *          'message' => (string) 'The media has not been copied',
     *          'media'  => [
     *              'from'    => (string) 'a/b/c/d/my_picture.jpg',
     *              'to'      => [
     *                  'filePath' => (string) '/tmp/files/identifier/code/',
     *                  'filename' => (string) 'my picture.jpg'
     *              ],
     *              'storage' => (string) 'catalogStorage',
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
     * Fetch a media to the target
     *
     * @param array $media
     */
    protected function fetch(array $media)
    {
        try {
            $filesystem = $this->filesystemProvider->getFilesystem($media['storage']);
            $this->mediaFetcher->fetch($filesystem, $media['from'], $media['to']);
        } catch (FileTransferException $e) {
            $this->addError(
                $media,
                sprintf('The media has not been found or is not currently available', $e->getMessage())
            );
        } catch (\LogicException $e) {
            $this->addError($media, sprintf('The media has not been copied. %s', $e->getMessage()));
        }
    }

    /**
     * @param array  $media
     * @param string $message
     */
    protected function addError(array $media, $message)
    {
        $this->errors[] = [
            'message' => $message,
            'media'   => $media,
        ];
    }
}
