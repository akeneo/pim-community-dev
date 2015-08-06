<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Upload;

use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * Manage upload of an asset file
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class UploadChecker implements UploadCheckerInterface
{
    /** @var UploaderInterface */
    protected $uploader;

    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /** @var string */
    protected $checkStatus;

    /**
     * @param UploaderInterface        $uploader
     * @param AssetRepositoryInterface $assetRepository
     */
    public function __construct(
        UploaderInterface $uploader,
        AssetRepositoryInterface $assetRepository
    ) {
        $this->uploader        = $uploader;
        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getCheckStatus()
    {
        return $this->checkStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidFilename($filename)
    {
        $this->checkStatus = UploadStatus::STATUS_NEW;

        $parsedName = $this->uploader->parseFilename($filename);

        if (null !== $parsedName['code']) {
            $assetCode  = $parsedName['code'];
            $localeCode = $parsedName['locale'];
            $valid      = $this->checkWithExistingAsset($assetCode, $localeCode);
        } else {
            $this->checkStatus = UploadStatus::STATUS_ERROR_CODE;
            $valid             = false;
        }

        if (true === $valid) {
            $uploadPath = $this->uploader->getUserUploadDir() . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($uploadPath)) {
                $valid             = false;
                $this->checkStatus = UploadStatus::STATUS_ERROR_EXISTS;
            }
        }

        if (true === $valid) {
            $schedulePath = $this->uploader->getUserScheduleDir() . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($schedulePath)) {
                $valid             = false;
                $this->checkStatus = UploadStatus::STATUS_ERROR_EXISTS;
            }
        }

        return $valid;
    }

    /**
     * Check with existing assets
     *
     * @param string      $assetCode
     * @param string|null $localeCode
     *
     * @return bool
     */
    protected function checkWithExistingAsset($assetCode, $localeCode = null)
    {
        /** @var AssetInterface $asset */
        $asset = $this->assetRepository->findOneByIdentifier($assetCode);

        if (null === $asset
            || (empty($asset->getLocales()) && null === $localeCode)
            || (in_array($localeCode, array_keys($asset->getLocales())))
        ) {
            return true;
        }

        $this->checkStatus = UploadStatus::STATUS_ERROR_LOCALE;

        return false;
    }
}
