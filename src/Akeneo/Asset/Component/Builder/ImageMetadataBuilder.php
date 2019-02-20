<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Asset\Component\Builder;

use Akeneo\Tool\Component\FileMetadata\FileMetadataBagInterface;
use Akeneo\Tool\Component\FileMetadata\FileMetadataReaderFactoryInterface;

/**
 * Builder for ImageMetadata
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class ImageMetadataBuilder implements MetadataBuilderInterface
{
    /** @var FileMetadataReaderFactoryInterface */
    protected $metaReaderFactory;

    /** @var MetadataBuilderInterface */
    protected $fileMetaBuidler;

    /** @var string */
    protected $imageMetadataClass;

    /**
     * @param FileMetadataReaderFactoryInterface $metaReaderFactory
     * @param MetadataBuilderInterface           $fileMetaBuidler
     * @param string                             $imageMetadataClass
     */
    public function __construct(
        FileMetadataReaderFactoryInterface $metaReaderFactory,
        MetadataBuilderInterface $fileMetaBuidler,
        $imageMetadataClass = 'Akeneo\Asset\Component\Model\ImageMetadata'
    ) {
        $this->metaReaderFactory = $metaReaderFactory;
        $this->fileMetaBuidler = $fileMetaBuidler;
        $this->imageMetadataClass = $imageMetadataClass;
    }

    /**
     * {@inheritdoc}
     */
    public function build(\SplFileInfo $file)
    {
        $fileMetadata = $this->fileMetaBuidler->build($file);

        $metadataReader = $this->getMetadataReader($file);
        $metadataReader->all($file);
        $metadata = $metadataReader->getMetadata();

        $imageMetadata = new $this->imageMetadataClass($fileMetadata);
        $imageMetadata->setExifResolution($this->truncateMetadata($this->getHumanReadableResolution($metadata)));
        $imageMetadata->setExifDateTimeOriginal($this->truncateMetadata($metadata->get('exif.EXIF.DateTimeOriginal')));
        $imageMetadata->setExifCameraMake($this->truncateMetadata($metadata->get('exif.IFD0.Make')));
        $imageMetadata->setExifCameraModel($this->truncateMetadata($metadata->get('exif.IFD0.Model')));
        $imageMetadata->setExifSizeWidth($this->truncateMetadata($metadata->get('exif.COMPUTED.Width')));
        $imageMetadata->setExifSizeHeight($this->truncateMetadata($metadata->get('exif.COMPUTED.Height')));
        $imageMetadata->setExifOrientation($this->truncateMetadata($metadata->get('exif.IFD0.Orientation')));
        $imageMetadata->setExifCopyright($this->truncateMetadata($metadata->get('exif.IFD0.Copyright')));
        $imageMetadata->setExifKeywords($this->truncateMetadata($metadata->get('exif.IFD0.Keywords')));
        $imageMetadata->setExifTitle($this->truncateMetadata($metadata->get('exif.IFD0.Title')));
        $imageMetadata->setExifDescription($this->truncateMetadata($metadata->get('exif.IFD0.Subject')));
        $imageMetadata->setExifColorSpace($this->truncateMetadata($metadata->get('exif.EXIF.ColorSpace')));
        $imageMetadata->setIptcKeywords($this->truncateMetadata(implode(',', $metadata->get('iptc.Keywords', []))));
        $imageMetadata->setIptcLocationCountry($this->truncateMetadata($metadata->get('iptc.LocationName')));
        $imageMetadata->setIptcLocationCity($this->truncateMetadata($metadata->get('iptc.City')));

        return $imageMetadata;
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return \Akeneo\Tool\Component\FileMetadata\FileMetadataReaderInterface
     */
    protected function getMetadataReader(\SplFileInfo $file)
    {
        return $this->metaReaderFactory->create($file);
    }

    /**
     * Returns a human readable resolution metadata with the given $metadata and null if not available.
     *
     * exif.IFD0.XResolution => '300/1' | '72/1'
     *
     * @param FileMetadataBagInterface $metadata
     *
     * @return string|null
     */
    protected function getHumanReadableResolution(FileMetadataBagInterface $metadata)
    {
        $allResolutionUnits = [
            1 => 'N/A',        // none
            2 => 'DPI',        // inches
            3 => 'Centimeters' // centimeters
        ];

        $resolution = 'undefined';
        $resolutionUnits = 'N/A';

        $exifResolution = $metadata->get('exif.IFD0.XResolution');
        $exifResolutionUnits = $metadata->get('exif.IFD0.ResolutionUnit');

        if (null === $exifResolution && null === $exifResolutionUnits) {
            return null;
        }

        if (null !== $exifResolution && false !== strpos($exifResolution, '/')) {
            $resolution = explode('/', $exifResolution)[0];
        }

        if (null !== $exifResolutionUnits && in_array($exifResolutionUnits, array_keys($allResolutionUnits))) {
            $resolutionUnits = $allResolutionUnits[$exifResolutionUnits];
        }

        return sprintf('%s %s', $resolution, $resolutionUnits);
    }

    /**
     * @param mixed $metadata
     *
     * @return mixed
     */
    private function truncateMetadata($metadata)
    {
        if (is_string($metadata)) {
            return substr($metadata, 0, 255);
        }

        return $metadata;
    }
}
