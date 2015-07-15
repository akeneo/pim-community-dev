<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Builder;

use Akeneo\Component\FileMetadata\FileMetadataBagInterface;
use Akeneo\Component\FileMetadata\FileMetadataReaderFactoryInterface;

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
        $imageMetadataClass = 'PimEnterprise\Component\ProductAsset\Model\ImageMetadata'
    ) {
        $this->metaReaderFactory  = $metaReaderFactory;
        $this->fileMetaBuidler    = $fileMetaBuidler;
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
        $imageMetadata->setExifResolution($this->getHumanReadableResolution($metadata));
        $imageMetadata->setExifDateTimeOriginal($metadata->get('exif.EXIF.DateTimeOriginal'));
        $imageMetadata->setExifCameraMake($metadata->get('exif.IFD0.Make'));
        $imageMetadata->setExifCameraModel($metadata->get('exif.IFD0.Model'));
        $imageMetadata->setExifSizeWidth($metadata->get('exif.COMPUTED.Width'));
        $imageMetadata->setExifSizeHeight($metadata->get('exif.COMPUTED.Height'));
        $imageMetadata->setExifOrientation($metadata->get('exif.IFD0.Orientation'));
        $imageMetadata->setExifCopyright($metadata->get('exif.IFD0.Copyright'));
        $imageMetadata->setExifKeywords($metadata->get('exif.IFD0.Keywords'));
        $imageMetadata->setExifTitle($metadata->get('exif.IFD0.Title'));
        $imageMetadata->setExifDescription($metadata->get('exif.IFD0.Subject'));
        $imageMetadata->setExifColorSpace($metadata->get('exif.EXIF.ColorSpace'));
        $imageMetadata->setIptcKeywords(implode(',', $metadata->get('iptc.Keywords', [])));
        $imageMetadata->setIptcLocationCountry($metadata->get('iptc.LocationName'));
        $imageMetadata->setIptcLocationCity($metadata->get('iptc.City'));

        return $imageMetadata;
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return \Akeneo\Component\FileMetadata\FileMetadataReaderInterface
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
}
