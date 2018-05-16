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

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
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

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param AssetRepositoryInterface  $assetRepository
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(
        AssetRepositoryInterface $assetRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->assetRepository = $assetRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * @param string $filename
     *
     * @return ParsedFilenameInterface
     */
    public function getParsedFilename($filename)
    {
        $locales = $this->localeRepository->findAll();

        return new ParsedFilename($locales, $filename);
    }

    /**
     * {@inheritdoc}
     */
    public function validateFilenameFormat(ParsedFilenameInterface $parsedFilename)
    {
        if (null === $parsedFilename->getAssetCode()) {
            throw new InvalidCodeException();
        }

        $locales = $this->localeRepository->findAll();
        if (null !== $parsedFilename->getLocaleCode() &&
            !$this->isLocaleActivated($locales, $parsedFilename->getLocaleCode())
        ) {
            throw new InvalidLocaleException();
        }

        if (!$this->validateWithExistingAssets($parsedFilename)) {
            throw new InvalidLocaleException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateUpload(ParsedFilenameInterface $parsedFilename, $tmpUploadDir, $tmpImportDir)
    {
        $this->validateFilenameFormat($parsedFilename);

        $uploadPath = $tmpUploadDir . DIRECTORY_SEPARATOR . $parsedFilename->getCleanFilename();
        if (file_exists($uploadPath)) {
            throw new DuplicateFileException();
        }

        $importPath = $tmpImportDir . DIRECTORY_SEPARATOR . $parsedFilename->getCleanFilename();
        if (file_exists($importPath)) {
            throw new DuplicateFileException();
        }
    }

    /**
     * Check if an uploaded file could be applied to an existing asset
     *
     * @param ParsedFilenameInterface $parsedFilename
     *
     * @throws InvalidLocaleException
     *
     * @return bool
     */
    protected function validateWithExistingAssets(ParsedFilenameInterface $parsedFilename)
    {
        $asset = $this->assetRepository->findOneByCode($parsedFilename->getAssetCode());

        if (null === $asset) {
            return true;
        }

        $assetLocales = $asset->getLocales();

        if ((empty($assetLocales) && null === $parsedFilename->getLocaleCode()) ||
            (in_array($parsedFilename->getLocaleCode(), array_keys($assetLocales)))
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check if a locale is activated
     *
     * @param LocaleInterface[] $locales
     * @param string            $localeCode
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    protected function isLocaleActivated(array $locales, $localeCode)
    {
        foreach ($locales as $locale) {
            if ($localeCode === $locale->getCode()) {
                return $locale->isActivated();
            }
        }

        throw new \RuntimeException(sprintf('Locale code %s is unknown', $localeCode));
    }
}
