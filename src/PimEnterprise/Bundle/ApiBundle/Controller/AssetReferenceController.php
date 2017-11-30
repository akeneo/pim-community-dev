<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\Controller;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Component\FileStorage\FilesystemProvider;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\StreamedFileResponse;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetReferenceController
{
    const NON_LOCALIZABLE_REFERENCE = 'no_locale';

    /** @var IdentifiableObjectRepositoryInterface */
    protected $assetRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /** @var FilesystemProvider */
    protected $filesystemProvider;

    /** @var FileFetcherInterface */
    protected $fileFetcher;

    /**
     * @param IdentifiableObjectRepositoryInterface $assetRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param FilesystemProvider                    $filesystemProvider
     * @param FileFetcherInterface                  $fileFetcher
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $assetRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher
    ) {
        $this->assetRepository = $assetRepository;
        $this->localeRepository = $localeRepository;
        $this->filesystemProvider = $filesystemProvider;
        $this->fileFetcher = $fileFetcher;
    }

    /**
     * @param string $assetCode
     * @param string $localeCode
     *
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return StreamedFileResponse
     *
     * @AclAncestor("pim_api_asset_reference_list")
     */
    public function downloadAction(string $assetCode, string $localeCode): StreamedFileResponse
    {
        $asset = $this->getAsset($assetCode);
        $locale = $this->getLocale($localeCode);
        $referenceFile = $this->getReferenceFile($asset, $locale);

        if (null === $locale) {
            $notFoundMessage = sprintf('Reference file for the asset "%s" does not exist.', $assetCode);
        } else {
            $notFoundMessage = sprintf(
                'Reference file for the asset "%s" and the locale "%s" does not exist.',
                $assetCode,
                $locale->getCode()
            );
        }

        if (null === $referenceFile) {
            throw new NotFoundHttpException($notFoundMessage);
        }

        $fs = $this->filesystemProvider->getFilesystem(FileStorage::ASSET_STORAGE_ALIAS);
        $options = [
            'headers' => [
                'Content-Type'        => $referenceFile->getMimeType(),
                'Content-Disposition' => sprintf('attachment; filename="%s"', $referenceFile->getOriginalFilename())
            ]
        ];

        try {
            return $this->fileFetcher->fetch($fs, $referenceFile->getKey(), $options);
        } catch (FileTransferException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (FileNotFoundException $e) {
            throw new NotFoundHttpException($notFoundMessage, $e);
        }
    }

    /**
     * @param string $assetCode
     *
     * @throws NotFoundHttpException
     *
     * @return AssetInterface
     */
    protected function getAsset(string $assetCode): AssetInterface
    {
        $asset = $this->assetRepository->findOneByIdentifier($assetCode);
        if (null === $asset) {
            throw new NotFoundHttpException(sprintf('Asset "%s" does not exist.', $assetCode));
        }

        return $asset;
    }

    /**
     * @param string $localeCode
     *
     * @throws NotFoundHttpException
     *
     * @return null|LocaleInterface
     */
    protected function getLocale(string $localeCode): ?LocaleInterface
    {
        if (static::NON_LOCALIZABLE_REFERENCE === $localeCode) {
            return null;
        }

        $locale = $this->localeRepository->findOneByIdentifier($localeCode);
        if (null === $locale) {
            throw new NotFoundHttpException(sprintf('Locale "%s" does not exist.', $localeCode));
        }

        return $locale;
    }

    /**
     * @param AssetInterface       $asset
     * @param null|LocaleInterface $locale
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return null|FileInfoInterface
     */
    protected function getReferenceFile(AssetInterface $asset, ?LocaleInterface $locale): ?FileInfoInterface
    {
        if ($asset->isLocalizable() && null === $locale) {
            throw new UnprocessableEntityHttpException(sprintf(
                'The asset "%s" is localizable, you cannot fetch one of its references without locale.',
                $asset->getCode()
            ));
        }

        if (!$asset->isLocalizable() && null !== $locale) {
            throw new UnprocessableEntityHttpException(sprintf(
                'The asset "%s" is not localizable, you cannot fetch its references with a locale.',
                $asset->getCode()
            ));
        }

        $reference = $asset->getReference($locale);
        if (null === $reference) {
            return null;
        }

        return $reference->getFileInfo();
    }
}
