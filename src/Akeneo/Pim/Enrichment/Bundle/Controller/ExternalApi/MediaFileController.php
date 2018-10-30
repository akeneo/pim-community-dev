<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\ExternalApi;

use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\Api\Exception\PaginationParametersException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
use Akeneo\Tool\Component\Api\Pagination\PaginatorInterface;
use Akeneo\Tool\Component\Api\Pagination\ParameterValidatorInterface;
use Akeneo\Tool\Component\Api\Repository\ApiResourceRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Tool\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\FileStorage\StreamedFileResponse;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaFileController
{
    /** @var ApiResourceRepositoryInterface */
    protected $mediaRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var PaginatorInterface */
    protected $paginator;

    /** @var FilesystemProvider */
    protected $filesystemProvider;

    /** @var FileFetcherInterface */
    protected $fileFetcher;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $productModelRepository;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var SaverInterface */
    protected $productSaver;

    /** @var ObjectUpdaterInterface */
    protected $productModelUpdater;

    /** @var SaverInterface */
    protected $productModelSaver;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var SaverInterface */
    protected $fileInfoSaver;

    /** @var FileStorerInterface */
    protected $fileStorer;

    /** @var RemoverInterface */
    protected $remover;

    /** @var RouterInterface */
    protected $router;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param ApiResourceRepositoryInterface        $mediaRepository
     * @param NormalizerInterface                   $normalizer
     * @param ParameterValidatorInterface           $parameterValidator
     * @param PaginatorInterface                    $paginator
     * @param FilesystemProvider                    $filesystemProvider
     * @param FileFetcherInterface                  $fileFetcher
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param ObjectUpdaterInterface                $productUpdater
     * @param SaverInterface                        $productSaver
     * @param ValidatorInterface                    $validator
     * @param SaverInterface                        $fileInfoSaver
     * @param FileStorerInterface                   $fileStorer
     * @param RemoverInterface                      $remover
     * @param RouterInterface                       $router
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     * @param ObjectUpdaterInterface                $productModelUpdater
     * @param SaverInterface                        $productModelSaver
     * @param array                                 $apiConfiguration
     */
    public function __construct(
        ApiResourceRepositoryInterface $mediaRepository,
        NormalizerInterface $normalizer,
        ParameterValidatorInterface $parameterValidator,
        PaginatorInterface $paginator,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        IdentifiableObjectRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        ValidatorInterface $validator,
        SaverInterface $fileInfoSaver,
        FileStorerInterface $fileStorer,
        RemoverInterface $remover,
        RouterInterface $router,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        ObjectUpdaterInterface $productModelUpdater,
        SaverInterface $productModelSaver,
        array $apiConfiguration
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->normalizer = $normalizer;
        $this->parameterValidator = $parameterValidator;
        $this->paginator = $paginator;
        $this->filesystemProvider = $filesystemProvider;
        $this->fileFetcher = $fileFetcher;
        $this->productRepository = $productRepository;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
        $this->validator = $validator;
        $this->fileInfoSaver = $fileInfoSaver;
        $this->fileStorer = $fileStorer;
        $this->remover = $remover;
        $this->router = $router;
        $this->apiConfiguration = $apiConfiguration;
        $this->productModelRepository = $productModelRepository;
        $this->productModelUpdater = $productModelUpdater;
        $this->productModelSaver = $productModelSaver;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws HttpException
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $code)
    {
        $media = $this->mediaRepository->findOneByIdentifier(urldecode($code));
        if (null === $media || FileStorage::CATALOG_STORAGE_ALIAS !== $media->getStorage()) {
            throw new NotFoundHttpException(sprintf('Media file "%s" does not exist.', $code));
        }

        $mediaApi = $this->normalizer->normalize($media, 'external_api');

        return new JsonResponse($mediaApi);
    }

    /**
     * @param Request $request
     *
     * @throws HttpException
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        try {
            $this->parameterValidator->validate($request->query->all());
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $defaultParameters = [
            'page'       => 1,
            'limit'      => $this->apiConfiguration['pagination']['limit_by_default'],
            'with_count' => 'false',
        ];

        $queryParameters = array_merge($defaultParameters, $request->query->all());

        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);
        $criteria = ['storage' => FileStorage::CATALOG_STORAGE_ALIAS];
        $medias = $this->mediaRepository->searchAfterOffset($criteria, [], $queryParameters['limit'], $offset);

        $parameters = [
            'query_parameters' => $queryParameters,
            'list_route_name'  => 'pim_api_media_file_list',
            'item_route_name'  => 'pim_api_media_file_get',
        ];

        $count = true === $request->query->getBoolean('with_count') ? $this->mediaRepository->count($criteria) : null;

        $paginatedMedias = $this->paginator->paginate(
            $this->normalizer->normalize($medias, 'external_api'),
            $parameters,
            $count
        );

        return new JsonResponse($paginatedMedias);
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return StreamedFileResponse
     */
    public function downloadAction(Request $request, $code)
    {
        $filename = urldecode($code);
        $fileInfo = $this->mediaRepository->findOneBy([
            'key'     => $filename,
            'storage' => FileStorage::CATALOG_STORAGE_ALIAS
        ]);

        if (null === $fileInfo) {
            throw new NotFoundHttpException(sprintf('Media file "%s" does not exist.', $filename));
        }

        $fs = $this->filesystemProvider->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS);
        $options = [
            'headers' => [
                'Content-Type'        => $fileInfo->getMimeType(),
                'Content-Disposition' => sprintf('attachment; filename="%s"', $fileInfo->getOriginalFilename())
            ]
        ];

        try {
            return $this->fileFetcher->fetch($fs, $filename, $options);
        } catch (FileTransferException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (FileNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('Media file "%s" is not present on the filesystem.', $filename), $e);
        }
    }

    /**
     * @param Request $request
     *
     * @throws HttpException
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        if ($request->request->has('product') && $request->request->has('product_model')) {
            throw new UnprocessableEntityHttpException('You should give either a "product" or a "product_model" key.');
        }

        if ($request->request->has('product')) {
            return $this->createProductMedia($request);
        }

        if ($request->request->has('product_model')) {
            return $this->createProductModelMedia($request);
        }

        throw new UnprocessableEntityHttpException(
            'You should at least give one of the following properties: "product" or "product_model".'
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    protected function createProductModelMedia(Request $request): Response
    {
        $productModelInfos = $this->getProductModelDecodedContent($request->request->get('product_model'));
        $productModel = $this->productModelRepository->findOneByIdentifier($productModelInfos['code']);
        if (null === $productModel) {
            throw new UnprocessableEntityHttpException(
                sprintf('Product model "%s" does not exist.', $productModelInfos['code'])
            );
        }

        $fileInfo = $this->storeFile($request->files);
        $this->linkFileToProductModel($fileInfo, $productModel, $productModelInfos);

        $response = new Response(null, Response::HTTP_CREATED);
        $route = $this->router->generate(
            'pim_api_media_file_get',
            ['code' => $fileInfo->getKey()],
            Router::ABSOLUTE_URL
        );

        $response->headers->set('Location', $route);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @throws HttpException
     *
     * @return Response
     */
    protected function createProductMedia(Request $request)
    {
        $productInfos = $this->getProductDecodedContent($request->request->get('product'));
        $product = $this->productRepository->findOneByIdentifier($productInfos['identifier']);
        if (null === $product) {
            throw new UnprocessableEntityHttpException(
                sprintf('Product "%s" does not exist.', $productInfos['identifier'])
            );
        }

        $fileInfo = $this->storeFile($request->files);
        $this->linkFileToProduct($fileInfo, $product, $productInfos);

        $response = new Response(null, Response::HTTP_CREATED);
        $route = $this->router->generate(
            'pim_api_media_file_get',
            ['code' => $fileInfo->getKey()],
            Router::ABSOLUTE_URL
        );

        $response->headers->set('Location', $route);

        return $response;
    }

    /**
     * @param FileInfoInterface $fileInfo
     * @param ProductInterface  $product
     * @param array             $productInfos
     *
     * @throws HttpException
     *
     * @return ProductInterface
     */
    protected function linkFileToProduct(
        FileInfoInterface $fileInfo,
        ProductInterface $product,
        array $productInfos
    ): ProductInterface {
        $productValues = ['values' => [
            $productInfos['attribute'] => [
                [
                    'locale' => $productInfos['locale'],
                    'scope'  => $productInfos['scope'],
                    'data'   => $fileInfo->getKey()
                ]
            ]
        ]];

        try {
            $this->productUpdater->update($product, $productValues);
        } catch (PropertyException $e) {
            $this->remover->remove($fileInfo);

            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $violations = $this->validator->validate($product);
        if ($violations->count() > 0) {
            $this->remover->remove($fileInfo);

            throw new ViolationHttpException($violations);
        }

        $this->productSaver->save($product);

        return $product;
    }

    /**
     * @param FileInfoInterface     $fileInfo
     * @param ProductModelInterface $productModel
     * @param array                 $productModelInfos
     *
     * @throws HttpException
     *
     * @return ProductModelInterface
     */
    protected function linkFileToProductModel(
        FileInfoInterface $fileInfo,
        ProductModelInterface $productModel,
        array $productModelInfos
    ): ProductModelInterface {
        $productModelValues = ['values' => [
            $productModelInfos['attribute'] => [
                [
                    'locale' => $productModelInfos['locale'],
                    'scope'  => $productModelInfos['scope'],
                    'data'   => $fileInfo->getKey()
                ]
            ]
        ]];

        try {
            $this->productModelUpdater->update($productModel, $productModelValues);
        } catch (PropertyException $e) {
            $this->remover->remove($fileInfo);

            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $violations = $this->validator->validate($productModel);
        if ($violations->count() > 0) {
            $this->remover->remove($fileInfo);

            throw new ViolationHttpException($violations);
        }

        $this->productModelSaver->save($productModel);

        return $productModel;
    }

    /**
     * @param FileBag $files
     *
     * @throws HttpException
     *
     * @return FileInfoInterface
     */
    protected function storeFile(FileBag $files)
    {
        if (!$files->has('file')) {
            throw new UnprocessableEntityHttpException('Property "file" is required.');
        }

        try {
            $fileInfo = $this->fileStorer->store($files->get('file'), FileStorage::CATALOG_STORAGE_ALIAS, true);
        } catch (FileTransferException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        } catch (FileRemovalException $e) {
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
     * @param string $content
     *
     * @throws HttpException
     *
     * @return array
     */
    protected function getProductDecodedContent($content): array
    {
        $decodedContent = json_decode($content, true);
        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        if (!isset($decodedContent['identifier']) || !isset($decodedContent['attribute']) ||
            !array_key_exists('locale', $decodedContent) || !array_key_exists('scope', $decodedContent)) {
            throw new UnprocessableEntityHttpException(
                'Product property must contain "identifier", "attribute", "locale" and "scope" properties.'
            );
        }

        return $decodedContent;
    }

    /**
     * @param string $content
     *
     * @throws HttpException
     *
     * @return array
     */
    protected function getProductModelDecodedContent($content): array
    {
        $decodedContent = json_decode($content, true);
        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        if (!isset($decodedContent['code']) || !isset($decodedContent['attribute']) ||
            !array_key_exists('locale', $decodedContent) || !array_key_exists('scope', $decodedContent)) {
            throw new UnprocessableEntityHttpException(
                'Product model property must contain "code", "attribute", "locale" and "scope" properties.'
            );
        }

        return $decodedContent;
    }
}
