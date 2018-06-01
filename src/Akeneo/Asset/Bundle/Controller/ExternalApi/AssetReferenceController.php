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

namespace Akeneo\Asset\Bundle\Controller\ExternalApi;

use Akeneo\Asset\Component\Model\LocaleCode;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Akeneo\Asset\Component\Factory\ReferenceFactory;
use Akeneo\Asset\Component\FileStorage;
use Akeneo\Asset\Component\Finder\AssetFinderInterface;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Asset\Component\ProcessedItem;
use Akeneo\Asset\Component\ProcessedItemList;
use Akeneo\Asset\Component\Updater\FilesUpdaterInterface;
use Akeneo\Asset\Component\VariationsCollectionFilesGeneratorInterface;
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

    /** @var VariationsCollectionFilesGeneratorInterface */
    protected $variationFilesGenerator;

    /** @var AssetFinderInterface */
    protected $assetFinder;

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
     * @param VariationsCollectionFilesGeneratorInterface $variationFilesGenerator
     * @param AssetFinderInterface                        $assetFinder
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
        SaverInterface $assetSaver,
        VariationsCollectionFilesGeneratorInterface $variationFilesGenerator,
        AssetFinderInterface $assetFinder
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
        $this->variationFilesGenerator = $variationFilesGenerator;
        $this->assetFinder = $assetFinder;
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
        $localeCode = new LocaleCode($localeCode);

        if (null === $reference || null === $reference->getFileInfo()) {
            $localizableMessage = $localeCode->hasValidCode() ? sprintf(' and the locale "%s"', $localeCode) : '';
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
            $localizableMessage = $localeCode->hasValidCode() ? sprintf(' and the locale "%s"', $localeCode) : '';
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
        $localeCode = new LocaleCode($localeCode);

        if (null === $reference || null === $reference->getFileInfo()) {
            $localizableMessage = $localeCode->hasValidCode() ? sprintf(' and the locale "%s"', $localeCode) : '';
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
    public function createAction(Request $request, string $code, string $localeCode): Response
    {
        $asset = $this->getAsset($code);
        $reference = $this->getReference($code, $localeCode);

        if (null === $reference) {
            $locale = $this->getLocale($localeCode);
            $reference = $this->referenceFactory->create($locale);
            $reference->setAsset($asset);
        }

        $fileInfo = $this->storeFile($request->files);
        $reference->setFileInfo($fileInfo);
        $this->assetFilesUpdater->resetAllVariationsFiles($reference, false);
        $this->validateAsset($asset);
        $this->assetSaver->save($asset);

        $variations = $this->assetFinder->retrieveVariationsNotGeneratedForAReference($reference);
        $variationItems = $this->variationFilesGenerator->generate($variations, false);

        $response = $this->createResponseWithErrors($variationItems);
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
        $localeCode = new LocaleCode($localeCode);

        if ($localeCode->hasValidCode()) {
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
                'The asset "%s" is localizable, you must provide an existing locale code. "%s" is only allowed when the asset is not localizable.',
                $code,
                (string) new LocaleCode($localeCode)
            ));
        }

        if (!$asset->isLocalizable() && null !== $locale) {
            throw new UnprocessableEntityHttpException(sprintf(
                'The asset "%s" is not localizable, you must provide the string "%s" as a locale.',
                $asset->getCode(),
                (string) new LocaleCode($localeCode)
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

    /**
     * If a variation generation problem occurred, errors are sent into the message body.
     *
     * As the asset has been well created, it returns HTTP code 201, even if errors occurred
     * during the variation generation.
     *
     * @param ProcessedItemList $variationItems
     *
     * @return Response
     */
    protected function createResponseWithErrors(ProcessedItemList $variationItems): Response
    {
        if (!$variationItems->hasItemInState(ProcessedItem::STATE_ERROR)) {
            return new Response(null, Response::HTTP_CREATED);
        }

        $body = [
            'message' => 'Some variation files were not generated properly.',
            'errors' => []
        ];

        foreach ($variationItems as $variationItem) {
            if (ProcessedItem::STATE_ERROR === $variationItem->getState()) {
                $locale = $variationItem->getItem()->getLocale();
                $error = [
                    'message' => $variationItem->getException()->getMessage(),
                    'scope' => $variationItem->getItem()->getChannel()->getCode(),
                    'locale' => null !== $locale ? $locale->getCode() : null
                ];

                $body['errors'][] = $error;
            }
        }

        return new JsonResponse($body, Response::HTTP_CREATED);
    }
}
