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
 * ImageMetadataInterface stores metadata for Image file type
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
interface ImageMetadataInterface extends FileMetadataInterface
{
    /**
     * @return string
     */
    public function getExifResolution();

    /**
     * @param string $exifResolution
     *
     * @return ImageMetadataInterface
     */
    public function setExifResolution($exifResolution);

    /**
     * @return string
     */
    public function getExifDateTimeOriginal();

    /**
     * @param string $exifDateTimeOriginal
     *
     * @return ImageMetadataInterface
     */
    public function setExifDateTimeOriginal($exifDateTimeOriginal);

    /**
     * @return string
     */
    public function getExifCameraMake();

    /**
     * @param string $exifCameraMake
     *
     * @return ImageMetadataInterface
     */
    public function setExifCameraMake($exifCameraMake);

    /**
     * @return string
     */
    public function getExifCameraModel();

    /**
     * @param string $exifCameraModel
     *
     * @return ImageMetadataInterface
     */
    public function setExifCameraModel($exifCameraModel);

    /**
     * @return string
     */
    public function getExifSizeWidth();

    /**
     * @param string $exifSizeWidth
     *
     * @return ImageMetadataInterface
     */
    public function setExifSizeWidth($exifSizeWidth);

    /**
     * @return string
     */
    public function getExifSizeHeight();

    /**
     * @param string $exifSizeHeight
     *
     * @return ImageMetadataInterface
     */
    public function setExifSizeHeight($exifSizeHeight);

    /**
     * @return string
     */
    public function getExifOrientation();

    /**
     * @param string $exifOrientation
     *
     * @return ImageMetadataInterface
     */
    public function setExifOrientation($exifOrientation);

    /**
     * @return string
     */
    public function getExifCopyright();

    /**
     * @param string $exifCopyright
     *
     * @return ImageMetadataInterface
     */
    public function setExifCopyright($exifCopyright);

    /**
     * @return string
     */
    public function getExifKeywords();

    /**
     * @param string $exifKeywords
     *
     * @return ImageMetadataInterface
     */
    public function setExifKeywords($exifKeywords);

    /**
     * @return string
     */
    public function getExifTitle();

    /**
     * @param string $exifTitle
     *
     * @return ImageMetadataInterface
     */
    public function setExifTitle($exifTitle);

    /**
     * @return string
     */
    public function getExifDescription();

    /**
     * @param string $exifDescription
     *
     * @return ImageMetadataInterface
     */
    public function setExifDescription($exifDescription);

    /**
     * @return string
     */
    public function getExifColorSpace();

    /**
     * @param string $exifColorSpace
     *
     * @return ImageMetadataInterface
     */
    public function setExifColorSpace($exifColorSpace);

    /**
     * @return string
     */
    public function getIptcKeywords();

    /**
     * @param string $iptcKeywords
     *
     * @return ImageMetadataInterface
     */
    public function setIptcKeywords($iptcKeywords);

    /**
     * @return string
     */
    public function getIptcLocationCountry();

    /**
     * @param string $iptcLocationCountry
     *
     * @return ImageMetadataInterface
     */
    public function setIptcLocationCountry($iptcLocationCountry);

    /**
     * @return string
     */
    public function getIptcLocationCity();

    /**
     * @param string $iptcLocationCity
     *
     * @return ImageMetadataInterface
     */
    public function setIptcLocationCity($iptcLocationCity);
}
