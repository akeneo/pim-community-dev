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

use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;

/**
 * Check uploaded files
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class UploadChecker implements UploadCheckerInterface
{
    /** @var AssetRepositoryInterface */
    protected $assetRepository;

    /**
     * @param AssetRepositoryInterface $assetRepository
     */
    public function __construct(AssetRepositoryInterface $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function isError($uploadStatus)
    {
        $errorStatus = [
            UploadStatus::STATUS_ERROR_CODE,
            UploadStatus::STATUS_ERROR_LOCALE,
            UploadStatus::STATUS_ERROR_EXISTS,
            UploadStatus::STATUS_ERROR_CONFLICTS
        ];

        return in_array($uploadStatus, $errorStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function checkFilename($filename, $tmpUploadDir, $tmpScheduleDir)
    {
        $parsedName = $this->parseFilename($filename);

        if (null !== $parsedName['code']) {
            $checkStatus = $this->checkWithExistingAsset($parsedName['code'], $parsedName['locale']);
        } else {
            return UploadStatus::STATUS_ERROR_CODE;
        }

        $uploadPath = $tmpUploadDir . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($uploadPath)) {
            return UploadStatus::STATUS_ERROR_EXISTS;
        }

        $schedulePath = $tmpScheduleDir . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($schedulePath)) {
            return UploadStatus::STATUS_ERROR_EXISTS;
        }

        return $checkStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function parseFilename($filename)
    {
        $parsed = [
            'code'   => null,
            'locale' => null,
        ];

        $patternCodePart   = '[a-zA-Z0-9_]+';
        $patternLocalePart = '[a-z]{2}_[A-Z]{2}';

        $pattern = sprintf('/^
            (%s)        #asset code
            (?:-(%s))?  #locale code (optionnal)
            \.[^.]+     #file extension
            $/x', $patternCodePart, $patternLocalePart);

        if (preg_match($pattern, $filename, $matches)) {
            $parsed['code']   = $matches[1];
            $parsed['locale'] = isset($matches[2]) ? $matches[2] : null;
        }

        return $parsed;
    }

    /**
     * Check if an uploaded file could be applied to an existing asset
     *
     * @param string      $assetCode
     * @param string|null $localeCode
     *
     * @return bool
     */
    protected function checkWithExistingAsset($assetCode, $localeCode = null)
    {
        $asset = $this->assetRepository->findOneByIdentifier($assetCode);

        if (null === $asset) {
            return UploadStatus::STATUS_NEW;
        }

        $assetLocales = $asset->getLocales();

        if ((empty($assetLocales) && null === $localeCode) ||
            (in_array($localeCode, array_keys($asset->getLocales())))
        ) {
            return UploadStatus::STATUS_UPDATED;
        }

        return UploadStatus::STATUS_ERROR_LOCALE;
    }
}
