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
use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\FilesystemProvider;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Factory\ReferenceFactory;
use PimEnterprise\Component\ProductAsset\Factory\VariationFactory;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
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
class AssetVariationController
{
    public const NON_LOCALIZABLE_VARIATION = 'no-locale';

    /** @var IdentifiableObjectRepositoryInterface */
    protected $assetRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

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

    /** @var SaverInterface */
    protected $assetSaver;

    /** @var VariationFactory */
    protected $variationFactory;

    /**
     * @param IdentifiableObjectRepositoryInterface $assetRepository
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param FilesystemProvider                    $filesystemProvider
     * @param FileFetcherInterface                  $fileFetcher
     * @param NormalizerInterface                   $normalizer
     * @param ValidatorInterface                    $validator
     * @param SaverInterface                        $fileInfoSaver
     * @param FileStorerInterface                   $fileStorer
     * @param ReferenceFactory                      $referenceFactory
     * @param RouterInterface                       $router
     * @param SaverInterface                        $assetSaver
     * @param VariationFactory                      $variationFactory
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $assetRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        NormalizerInterface $normalizer,
        ValidatorInterface $validator,
        SaverInterface $fileInfoSaver,
        FileStorerInterface $fileStorer,
        ReferenceFactory $referenceFactory,
        RouterInterface $router,
        SaverInterface $assetSaver,
        VariationFactory $variationFactory
    ) {
        $this->assetRepository = $assetRepository;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->filesystemProvider = $filesystemProvider;
        $this->fileFetcher = $fileFetcher;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->fileInfoSaver = $fileInfoSaver;
        $this->fileStorer = $fileStorer;
        $this->referenceFactory = $referenceFactory;
        $this->router = $router;
        $this->assetSaver = $assetSaver;
        $this->variationFactory = $variationFactory;
    }

    /**
     * @param string $code
     * @param string $channelCode
     * @param string $localeCode
     *
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_list")
     */
    public function downloadAction(string $code, string $channelCode, string $localeCode): Response
    {
        $variation = $this->getVariation($code, $channelCode, $localeCode);

        if (null === $variation || null === $variation->getFileInfo()) {
            $localizableMessage = self::NON_LOCALIZABLE_VARIATION !== $localeCode ? sprintf(' and the locale "%s"', $localeCode) : '';
            $notFoundMessage = sprintf(
                'Variation file for the asset "%s" and the channel "%s"%s does not exist.',
                $code,
                $channelCode,
                $localizableMessage
            );

            throw new NotFoundHttpException($notFoundMessage);
        }

        $variationFile = $variation->getFileInfo();

        $fs = $this->filesystemProvider->getFilesystem(FileStorage::ASSET_STORAGE_ALIAS);
        $options = [
            'headers' => [
                'Content-Type'        => $variationFile->getMimeType(),
                'Content-Disposition' => sprintf('attachment; filename="%s"', $variationFile->getOriginalFilename())
            ]
        ];

        try {
            return $this->fileFetcher->fetch($fs, $variationFile->getKey(), $options);
        } catch (FileTransferException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (FileNotFoundException $e) {
            $localizableMessage = static::NON_LOCALIZABLE_VARIATION !== $localeCode
                ? sprintf(' and the locale "%s"', $localeCode)
                : '';
            $notFoundMessage = sprintf(
                'Variation file for the asset "%s" and the channel "%s"%s does not exist.',
                $code,
                $channelCode,
                $localizableMessage
            );

            throw new NotFoundHttpException($notFoundMessage, $e);
        }
    }

    /**
     * @param string $code
     * @param string $channelCode
     * @param string $localeCode
     *
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_list")
     */
    public function getAction(string $code, string $channelCode, string $localeCode): Response
    {
        $variation = $this->getVariation($code, $channelCode, $localeCode);

        if (null === $variation || null === $variation->getFileInfo()) {
            $localizableMessage = self::NON_LOCALIZABLE_VARIATION !== $localeCode ? sprintf(' and the locale "%s"', $localeCode) : '';
            $notFoundMessage = sprintf(
                'Variation file for the asset "%s" and the channel "%s"%s does not exist.',
                $code,
                $channelCode,
                $localizableMessage
            );

            throw new NotFoundHttpException($notFoundMessage);
        }

        $normalizedVariation = $this->normalizer->normalize($variation, 'external_api');

        return new JsonResponse($normalizedVariation);
    }

    /**
     * @param Request $request
     * @param string  $code
     * @param string  $channelCode
     * @param string  $localeCode
     *
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_edit")
     */
    public function createAction(Request $request, string $code, string $channelCode, string $localeCode): Response
    {
        $variation = $this->getVariation($code, $channelCode, $localeCode);
        $reference = $this->getReference($code, $localeCode);
        $asset = $this->getAsset($code);

        if (null === $variation && null === $reference) {
            $locale = $this->getLocale($localeCode);
            $reference = $this->referenceFactory->create($locale);
            $reference->setAsset($asset);

            $variation = $this->getVariation($code, $channelCode, $localeCode);
        } elseif (null === $variation && null !== $reference) {
            $channel = $this->getChannel($channelCode);
            $variation = $this->variationFactory->create($channel);
            $variation->setReference($reference);
        }

        $fileInfo = $this->storeFile($request->files);
        $variation->setSourceFileInfo($fileInfo);
        $variation->setFileInfo($fileInfo);
        $variation->setLocked(true);

        $this->validateAsset($asset);
        $this->assetSaver->save($asset);

        $response = new Response(null, Response::HTTP_CREATED);
        $route = $this->router->generate(
            'pimee_api_asset_variation_get',
            ['code' => $code, 'channelCode' => $channelCode, 'localeCode' => $localeCode],
            Router::ABSOLUTE_URL
        );

        $response->headers->set('Location', $route);

        return $response;
    }

    /**
     * @param string $channelCode
     *
     * @throws NotFoundHttpException
     *
     * @return ChannelInterface
     */
    protected function getChannel(string $channelCode): ChannelInterface
    {
        $channel = $this->channelRepository->findOneByIdentifier($channelCode);
        if (null === $channel) {
            throw new NotFoundHttpException(sprintf('Channel "%s" does not exist.', $channelCode));
        }

        return $channel;
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
        if (static::NON_LOCALIZABLE_VARIATION === $localeCode) {
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
     * @param string $channelCode
     * @param string $localeCode
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return null|VariationInterface
     */
    protected function getVariation(
        string $code,
        string $channelCode,
        string $localeCode
    ): ?VariationInterface {
        $channel = $this->getChannel($channelCode);
        $locale = $this->getLocale($localeCode);
        $this->validateLocaleIsActivatedForChannel($locale, $channel);

        $asset = $this->getAsset($code);

        if ($asset->isLocalizable() && null === $locale) {
            throw new UnprocessableEntityHttpException(sprintf(
                'The asset "%s" is localizable, you must provide an existing locale code. "no-locale" is only allowed when the asset is not localizable.',
                $code
            ));
        }

        if (!$asset->isLocalizable() && null !== $locale) {
            throw new UnprocessableEntityHttpException(sprintf(
                'The asset "%s" is not localizable, you must provide the string "no-locale" as a locale.',
                $asset->getCode()
            ));
        }

        $variation = $asset->getVariation($channel, $locale);

        return $variation;
    }

    /**
     * @param null|LocaleInterface $locale
     * @param ChannelInterface     $channel
     *
     * @throws NotFoundHttpException
     */
    protected function validateLocaleIsActivatedForChannel(?LocaleInterface $locale, ChannelInterface $channel): void
    {
        if (null !== $locale && !$channel->hasLocale($locale)) {
            throw new NotFoundHttpException(sprintf(
                'You cannot have a variation file for the locale "%s" and the channel "%s" as the locale "%s" is not activated for the channel "%s".',
                $locale->getCode(),
                $channel->getCode(),
                $locale->getCode(),
                $channel->getCode()
            ));
        }
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
                'The asset "%s" is localizable, you must provide an existing locale code. "no-locale" is only allowed when the asset is not localizable.',
                $code
            ));
        }

        if (!$asset->isLocalizable() && null !== $locale) {
            throw new UnprocessableEntityHttpException(sprintf(
                'The asset "%s" is not localizable, you must provide the string "no-locale" as a locale.',
                $asset->getCode()
            ));
        }

        return $asset->getReference($locale);
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
