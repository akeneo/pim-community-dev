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

use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
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

    /** @var LocaleInterface[] */
    protected $locales;

    /** @var LocaleManager */
    protected $localeManager;

    /**
     * @param AssetRepositoryInterface  $assetRepository
     * @param LocaleRepositoryInterface $localeRepository
     * @param LocaleManager             $localeManager
     */
    public function __construct(
        AssetRepositoryInterface $assetRepository,
        LocaleRepositoryInterface $localeRepository,
        LocaleManager $localeManager
    ) {
        $this->assetRepository = $assetRepository;
        $this->locales         = $localeRepository->findAll();
        $this->localeManager   = $localeManager;
    }

    /**
     * @param string $filename
     *
     * @return ParsedFilenameInterface
     */
    public function getParsedFilename($filename)
    {
        return new ParsedFilename($this->locales, $filename);
    }

    /**
     * {@inheritdoc}
     */
    public function validateFilenameFormat(ParsedFilenameInterface $parsedFilename)
    {
        if (null === $parsedFilename->getAssetCode()) {
            throw new InvalidCodeException();
        }

        if (null !== $parsedFilename->getLocaleCode() &&
            !$this->localeManager->isLocaleActivated($this->locales, $parsedFilename->getLocaleCode())
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
    public function validateUpload(ParsedFilenameInterface $parsedFilename, $tmpUploadDir, $tmpScheduleDir)
    {
        $this->validateFilenameFormat($parsedFilename);

        $uploadPath = $tmpUploadDir . DIRECTORY_SEPARATOR . $parsedFilename->getCleanFilename();
        if (file_exists($uploadPath)) {
            throw new DuplicateFileException();
        }

        $schedulePath = $tmpScheduleDir . DIRECTORY_SEPARATOR . $parsedFilename->getCleanFilename();
        if (file_exists($schedulePath)) {
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
}
