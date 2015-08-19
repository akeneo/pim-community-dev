<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Model;

/**
 * Implementation of ImageMetadataInterface
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class ImageMetadata extends FileMetadata implements ImageMetadataInterface
{
    /** @var string */
    protected $exifResolution;

    /** @var string */
    protected $exifDateTimeOriginal;

    /** @var string */
    protected $exifCameraMake;

    /** @var string */
    protected $exifCameraModel;

    /** @var string */
    protected $exifSizeWidth;

    /** @var string */
    protected $exifSizeHeight;

    /** @var string */
    protected $exifOrientation;

    /** @var string */
    protected $exifCopyright;

    /** @var string */
    protected $exifKeywords;

    /** @var string */
    protected $exifTitle;

    /** @var string */
    protected $exifDescription;

    /** @var string */
    protected $exifColorSpace;

    /** @var string */
    protected $iptcKeywords;

    /** @var string */
    protected $iptcLocationCountry;

    /** @var string */
    protected $iptcLocationCity;

    /**
     * @param FileMetadataInterface|null $fileMetadata
     */
    public function __construct(FileMetadataInterface $fileMetadata = null)
    {
        if (null !== $fileMetadata) {
            $this->modificationDatetime = $fileMetadata->getModificationDatetime();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExifResolution()
    {
        return $this->exifResolution;
    }

    /**
     * {@inheritdoc}
     */
    public function setExifResolution($exifResolution)
    {
        $this->exifResolution = $exifResolution;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExifDateTimeOriginal()
    {
        return $this->exifDateTimeOriginal;
    }

    /**
     * {@inheritdoc}
     */
    public function setExifDateTimeOriginal($exifDateTimeOriginal)
    {
        $this->exifDateTimeOriginal = $exifDateTimeOriginal;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExifCameraMake()
    {
        return $this->exifCameraMake;
    }

    /**
     * {@inheritdoc}
     */
    public function setExifCameraMake($exifCameraMake)
    {
        $this->exifCameraMake = $exifCameraMake;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExifCameraModel()
    {
        return $this->exifCameraModel;
    }

    /**
     * {@inheritdoc}
     */
    public function setExifCameraModel($exifCameraModel)
    {
        $this->exifCameraModel = $exifCameraModel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExifSizeWidth()
    {
        return $this->exifSizeWidth;
    }

    /**
     * {@inheritdoc}
     */
    public function setExifSizeWidth($exifSizeWidth)
    {
        $this->exifSizeWidth = $exifSizeWidth;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExifSizeHeight()
    {
        return $this->exifSizeHeight;
    }

    /**
     * {@inheritdoc}
     */
    public function setExifSizeHeight($exifSizeHeight)
    {
        $this->exifSizeHeight = $exifSizeHeight;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExifOrientation()
    {
        return $this->exifOrientation;
    }

    /**
     * {@inheritdoc}
     */
    public function setExifOrientation($exifOrientation)
    {
        $this->exifOrientation = $exifOrientation;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExifCopyright()
    {
        return $this->exifCopyright;
    }

    /**
     * {@inheritdoc}
     */
    public function setExifCopyright($exifCopyright)
    {
        $this->exifCopyright = $exifCopyright;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExifKeywords()
    {
        return $this->exifKeywords;
    }

    /**
     * {@inheritdoc}
     */
    public function setExifKeywords($exifKeywords)
    {
        $this->exifKeywords = $exifKeywords;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExifTitle()
    {
        return $this->exifTitle;
    }

    /**
     * {@inheritdoc}
     */
    public function setExifTitle($exifTitle)
    {
        $this->exifTitle = $exifTitle;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExifDescription()
    {
        return $this->exifDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function setExifDescription($exifDescription)
    {
        $this->exifDescription = $exifDescription;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExifColorSpace()
    {
        return $this->exifColorSpace;
    }

    /**
     * {@inheritdoc}
     */
    public function setExifColorSpace($exifColorSpace)
    {
        $this->exifColorSpace = $exifColorSpace;
    }

    /**
     * {@inheritdoc}
     */
    public function getIptcKeywords()
    {
        return $this->iptcKeywords;
    }

    /**
     * {@inheritdoc}
     */
    public function setIptcKeywords($iptcKeywords)
    {
        $this->iptcKeywords = $iptcKeywords;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIptcLocationCountry()
    {
        return $this->iptcLocationCountry;
    }

    /**
     * {@inheritdoc}
     */
    public function setIptcLocationCountry($iptcLocationCountry)
    {
        $this->iptcLocationCountry = $iptcLocationCountry;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIptcLocationCity()
    {
        return $this->iptcLocationCity;
    }

    /**
     * {@inheritdoc}
     */
    public function setIptcLocationCity($iptcLocationCity)
    {
        $this->iptcLocationCity = $iptcLocationCity;

        return $this;
    }
}
