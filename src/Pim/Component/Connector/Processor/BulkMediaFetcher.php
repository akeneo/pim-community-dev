<?php

namespace Pim\Component\Connector\Processor;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Component\FileStorage\FilesystemProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Connector\Writer\File\FileExporterPathGeneratorInterface;

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
    protected $mediaAttributeTypes;

    /** @var array */
    protected $errors;

    /**
     * @param FileFetcherInterface               $mediaFetcher
     * @param FilesystemProvider                 $filesystemProvider
     * @param FileExporterPathGeneratorInterface $fileExporterPath
     * @param array                              $mediaAttributeTypes
     */
    public function __construct(
        FileFetcherInterface $mediaFetcher,
        FilesystemProvider $filesystemProvider,
        FileExporterPathGeneratorInterface $fileExporterPath,
        array $mediaAttributeTypes
    ) {
        $this->errors = [];
        $this->mediaFetcher = $mediaFetcher;
        $this->fileExporterPath = $fileExporterPath;
        $this->mediaAttributeTypes = $mediaAttributeTypes;
        $this->filesystemProvider = $filesystemProvider;
    }

    /**
     * Fetch the media of the items to the target
     *
     * @param ArrayCollection $items
     * @param string          $target
     * @param string          $identifier
     */
    public function fetchAll(ArrayCollection $items, $target, $identifier)
    {
        $target = DIRECTORY_SEPARATOR !== substr($target, -1) ? $target . DIRECTORY_SEPARATOR : $target;

        foreach ($items as $value) {
            if (!$value instanceof ProductValueInterface) {
                throw new \InvalidArgumentException(
                    sprintf('Value is not an instance of %s.', ProductValueInterface::class)
                );
            }

            if (in_array($value->getAttribute()->getType(), $this->mediaAttributeTypes)
                && null !== $media = $value->getMedia()) {
                $exportPath = $this->fileExporterPath->generate(
                    [
                        'locale' => $value->getLocale(),
                        'scope'  => $value->getScope()
                    ],
                    [
                        'identifier' => $identifier,
                        'code'       => $value->getAttribute()->getCode()
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
