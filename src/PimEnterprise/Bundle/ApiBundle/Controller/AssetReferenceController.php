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

use Akeneo\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\FilesystemProvider;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Factory\ReferenceFactory;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Updater\FilesUpdaterInterface;
use PimEnterprise\Component\ProductAsset\VariationsCollectionFilesGeneratorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetReferenceController
{
    public const NON_LOCALIZABLE_REFERENCE = 'no_locale';

    /** @var IdentifiableObjectRepositoryInterface */
    protected $assetRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /** @var FilesystemProvider */
    protected $filesystemProvider;

    /** @var FileFetcherInterface */
    protected $fileFetcher;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var SaverInterface */
    protected $fileInfoSaver;

    /** @var FileStorerInterface */
    protected $fileStorer;

    /** @var ReferenceFactory */
    protected $referenceFactory;

    /** @var RouterInterface */
    protected $router;

    /** @var FilesUpdaterInterface */
    protected $assetFilesUpdater;

    /** @var SaverInterface */
    protected $assetSaver;

    /**
     * @param IdentifiableObjectRepositoryInterface       $assetRepository
     * @param IdentifiableObjectRepositoryInterface       $localeRepository
     * @param FilesystemProvider                          $filesystemProvider
     * @param FileFetcherInterface                        $fileFetcher
     * @param NormalizerInterface                         $normalizer
     * @param ValidatorInterface                          $validator
     * @param SaverInterface                              $fileInfoSaver
     * @param FileStorerInterface                         $fileStorer
     * @param ReferenceFactory                            $referenceFactory
     * @param RouterInterface                             $router
     * @param FilesUpdaterInterface                       $assetFilesUpdater
     * @param SaverInterface                              $assetSaver
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $assetRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        SaverInterface $fileInfoSaver,
        FileStorerInterface $fileStorer,
        ReferenceFactory $referenceFactory,
        RouterInterface $router,
        FilesUpdaterInterface $assetFilesUpdater,
        SaverInterface $assetSaver
    ) {
        $this->assetRepository = $assetRepository;
        $this->localeRepository = $localeRepository;
        $this->filesystemProvider = $filesystemProvider;
        $this->fileFetcher = $fileFetcher;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->fileInfoSaver = $fileInfoSaver;
        $this->fileStorer = $fileStorer;
        $this->referenceFactory = $referenceFactory;
        $this->router = $router;
        $this->assetFilesUpdater = $assetFilesUpdater;
        $this->assetSaver = $assetSaver;
    }

    /**
     * @param string $code
     * @param string $localeCode
     *
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_list")
     */
    public function downloadAction(string $code, string $localeCode): Response
    {
        $reference = $this->getReference($code, $localeCode);


        if (null === $reference || null === $reference->getFileInfo()) {
            $localizableMessage = self::NON_LOCALIZABLE_REFERENCE !== $localeCode ? sprintf(' and the locale "%s"', $localeCode) : '';
            $notFoundMessage = sprintf(
                'Reference file for the asset "%s"%s does not exist.',
                $code,
                $localizableMessage
            );

            throw new NotFoundHttpException($notFoundMessage);
        }

        $referenceFile = $reference->getFileInfo();

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
            $localizableMessage = static::NON_LOCALIZABLE_REFERENCE !== $localeCode
                ? sprintf(' and the locale "%s"', $localeCode)
                : '';
            $notFoundMessage = sprintf(
                'Reference file for the asset "%s"%s does not exist.',
                $code,
                $localizableMessage
            );

            throw new NotFoundHttpException($notFoundMessage, $e);
        }
    }

    /**
     * @param string $code
     * @param string $localeCode
     *
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_list")
     */
    public function getAction(string $code, string $localeCode): Response
    {
        $reference = $this->getReference($code, $localeCode);

        if (null === $reference || null === $reference->getFileInfo()) {
            $localizableMessage = self::NON_LOCALIZABLE_REFERENCE !== $localeCode ? sprintf(' and the locale "%s"', $localeCode) : '';
            $notFoundMessage = sprintf(
                'Reference file for the asset "%s"%s does not exist.',
                $code,
                $localizableMessage
            );

            throw new NotFoundHttpException($notFoundMessage);
        }

        $normalizedReference = $this->normalizer->normalize($reference, 'external_api');

        return new JsonResponse($normalizedReference);
    }

    /**
     * @param Request $request
     * @param string  $code
     * @param string  $localeCode
     *
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_edit")
     */
    public function partialUpdateAction(Request $request, string $code, string $localeCode): Response
    {
        $asset = $this->getAsset($code);
        $reference = $this->getReference($code, $localeCode);
        $isCreation = null === $reference || null === $reference->getFileInfo();

        if (null === $reference) {
            $locale = $this->getLocale($localeCode);
            $reference = $this->referenceFactory->create($locale);
            $reference->setAsset($asset);
        }

        $fileInfo = $this->storeFile($request->files);
        $reference->setFileInfo($fileInfo);
        $this->assetFilesUpdater->resetAllVariationsFiles($reference);
        $this->validateAsset($asset);
        $this->assetSaver->save($asset);

        $status = $isCreation ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;
        $response = new Response(null, $status);
        $route = $this->router->generate(
            'pimee_api_asset_reference_get',
            ['code' => $code, 'localeCode' => $localeCode],
            Router::ABSOLUTE_URL
        );

        $response->headers->set('Location', $route);

        return $response;
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
        if (null === $locale || false === $locale->isActivated()) {
            throw new NotFoundHttpException(sprintf('Locale "%s" does not exist or is not activated.', $localeCode));
        }

        return $locale;
    }

    /**
     * @param string $code
     * @param string $localeCode
     *
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return ReferenceInterface
     */
    protected function getReference(string $code, string $localeCode): ?ReferenceInterface
    {
        $locale = $this->getLocale($localeCode);

        $asset = $this->getAsset($code);

        if ($asset->isLocalizable() && null === $locale) {
            throw new UnprocessableEntityHttpException(sprintf(
                'The asset "%s" is localizable, you must provide an existing locale code. "no_locale" is only allowed when the asset is not localizable.',
                $code
            ));
        }

        if (!$asset->isLocalizable() && null !== $locale) {
            throw new UnprocessableEntityHttpException(sprintf(
                'The asset "%s" is not localizable, you must provide the string "no_locale" as a locale.',
                $asset->getCode()
            ));
        }

        return $asset->getReference($locale);
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return AssetInterface
     */
    protected function getAsset(string $code): AssetInterface
    {
        $asset = $this->assetRepository->findOneByIdentifier($code);
        if (null === $asset) {
            throw new NotFoundHttpException(sprintf('Asset "%s" does not exist.', $code));
        }

        return $asset;
    }

    /**
     * @param FileBag $files
     *
     * @throws HttpException
     *
     * @return FileInfoInterface
     */
    protected function storeFile(FileBag $files): FileInfoInterface
    {
        if (!$files->has('file')) {
            throw new UnprocessableEntityHttpException('Property "file" is required.');
        }

        try {
            $fileInfo = $this->fileStorer->store($files->get('file'), FileStorage::ASSET_STORAGE_ALIAS, true);
        } catch (FileTransferException | FileRemovalException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $violations = $this->validator->validate($fileInfo);
        if ($violations->count() > 0) {
            throw new ViolationHttpException($violations);
        }

        $this->fileInfoSaver->save($fileInfo);

        return $fileInfo;
    }

    /**
     * @param AssetInterface $asset
     *
     * @throws ViolationHttpException
     */
    protected function validateAsset(AssetInterface $asset): void
    {
        $violations = $this->validator->validate($asset);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }
}
