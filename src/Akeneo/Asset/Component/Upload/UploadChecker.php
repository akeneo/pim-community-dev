<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Component\Upload;

use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;
use Akeneo\Asset\Component\Upload\Exception\DuplicateFileException;
use Akeneo\Asset\Component\Upload\Exception\InvalidCodeException;
use Akeneo\Asset\Component\Upload\Exception\InvalidLocaleException;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

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

    protected $locales;

    /** @var FilesystemProvider */
    private $filesystemProvider;

    /**
     * @param AssetRepositoryInterface  $assetRepository
     * @param LocaleRepositoryInterface $localeRepository
     * @param FilesystemProvider|null   $filesystemProvider
     */
    public function __construct(
        AssetRepositoryInterface $assetRepository,
        LocaleRepositoryInterface $localeRepository,
        FilesystemProvider $filesystemProvider = null
    ) {
        $this->assetRepository = $assetRepository;
        $this->localeRepository = $localeRepository;
        $this->filesystemProvider = $filesystemProvider;
    }

    /**
     * @param string $filename
     *
     * @return ParsedFilenameInterface
     */
    public function getParsedFilename($filename)
    {
        return new ParsedFilename($this->getLocales(), $filename);
    }

    /**
     * {@inheritdoc}
     */
    public function validateFilenameFormat(ParsedFilenameInterface $parsedFilename)
    {
        if (null === $parsedFilename->getAssetCode()) {
            throw new InvalidCodeException();
        }

        $this->locales = $this->locales ?: $this->localeRepository->findAll();
        if (null !== $parsedFilename->getLocaleCode() &&
            !$this->isLocaleActivated($this->getLocales(), $parsedFilename->getLocaleCode())
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

        // TODO @pullup on master : remove this condition and use only $this->filesystemProvider
        if (null !== $this->filesystemProvider) {
            $uploadFileSystem = $this->filesystemProvider->getFilesystem('tmpAssetUpload');
            if ($uploadFileSystem->has($uploadPath)) {
                throw new DuplicateFileException();
            }
        } elseif (file_exists($uploadPath)) {
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

    /**
     * Get locales list, load it only when needed
     *
     * @return array
     */
    protected function getLocales()
    {
        if (null === $this->locales) {
            $this->locales = $this->localeRepository->findAll();
        }

        return $this->locales;
    }
}
