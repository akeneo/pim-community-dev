<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\ProductValueInterface;
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

    /** @var FileExporterPathGeneratorInterface */
    protected $fileExporterPath;

    /** @var array */
    protected $mediaAttributeTypes;

    /** @var array */
    protected $errors;

    /**
     * @param FileExporterInterface              $fileExporter
     * @param FileExporterPathGeneratorInterface $fileExporterPath
     * @param array                              $mediaAttributeTypes
     */
    public function __construct(
        FileExporterInterface $fileExporter,
        FileExporterPathGeneratorInterface $fileExporterPath,
        array $mediaAttributeTypes
    ) {
        $this->errors = [];
        $this->fileExporter = $fileExporter;
        $this->fileExporterPath = $fileExporterPath;
        $this->mediaAttributeTypes = $mediaAttributeTypes;
    }

    /**
     * Export the media of the items to the target
     *
     * @param ArrayCollection $items
     * @param string          $target
     * @param string          $identifier
     */
    public function exportAll(ArrayCollection $items, $target, $identifier)
    {
        foreach ($items as $value) {
            if (!$value instanceof ProductValueInterface) {
                throw new \InvalidArgumentException(
                    'Value is not an instance of Pim\Component\Catalog\Model\ProductValueInterface.'
                );
            }

            if (in_array($value->getAttribute()->getAttributeType(), $this->mediaAttributeTypes)
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

                $this->doCopy([
                    'from'    => $media->getKey(),
                    'to'      => $exportPath . $media->getOriginalFilename(),
                    'storage' => $media->getStorage()
                ], $target);
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
     *          'media'  => [
     *              'filePath' => (string),
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
     * Copy a media to the target
     *
     * @param array  $media
     * @param string $target
     */
    protected function doCopy(array $media, $target)
    {
        $target = $target . DIRECTORY_SEPARATOR . $media['to'];
        $fileSystem = new Filesystem();
        $fileSystem->mkdir(dirname($target));

        try {
            $this->fileExporter->export($media['from'], $target, $media['storage']);
        } catch (FileTransferException $e) {
            $this->addError($media, 'The media has not been found or is not currently available');
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
