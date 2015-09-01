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
use PimEnterprise\Component\ProductAsset\Upload\Exception\DuplicateFileException;
use PimEnterprise\Component\ProductAsset\Upload\Exception\InvalidCodeException;
use PimEnterprise\Component\ProductAsset\Upload\Exception\InvalidLocaleException;

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
    public function validateSchedule($filename, $tmpUploadDir, $tmpScheduleDir)
    {
        $parsedName = $this->parseFilename($filename);

        if (null === $parsedName['code']) {
            throw new InvalidCodeException();
        }

        if (!$this->validateWithExistingAssets($parsedName['code'], $parsedName['locale'])) {
            throw new InvalidLocaleException();
        }

        $uploadPath = $tmpUploadDir . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($uploadPath)) {
            throw new DuplicateFileException();
        }

        $schedulePath = $tmpScheduleDir . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($schedulePath)) {
            throw new DuplicateFileException();
        }
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

        $patternCodePart   = '[a-zA-Z0-9]+';
        $patternLocalePart = '[a-z]{2}(?:-[A-Za-z]{2,3})?_ [A-Z]{2}';

        $pattern = sprintf('/^
            (?P<code>%s)          #asset code
            (?:-(?P<locale>%s))?  #locale code (optionnal)
            \.[^.]+               #file extension
            $/x', $patternCodePart, $patternLocalePart);

        if (preg_match($pattern, $filename, $matches)) {
            $parsed['code']   = $matches['code'];
            $parsed['locale'] = isset($matches['locale']) ? $matches['locale'] : null;
        }

        return $parsed;
    }

    /**
     * Check if an uploaded file could be applied to an existing asset
     *
     * @param string      $assetCode
     * @param string|null $localeCode
     *
     * @throws InvalidLocaleException
     *
     * @return bool
     */
    protected function validateWithExistingAssets($assetCode, $localeCode = null)
    {
        $asset = $this->assetRepository->findOneByCode($assetCode);

        if (null === $asset) {
            return true;
        }

        $assetLocales = $asset->getLocales();

        if ((empty($assetLocales) && null === $localeCode) ||
            (in_array($localeCode, array_keys($assetLocales)))
        ) {
            return true;
        }

        return false;
    }
}
