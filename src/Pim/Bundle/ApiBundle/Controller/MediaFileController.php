<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Akeneo\Component\FileStorage\Exception\FileRemovalException;
use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\FileStorage\FilesystemProvider;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Component\FileStorage\StreamedFileResponse;
use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Api\Pagination\PaginatorInterface;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
use Pim\Component\Api\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\FileStorage;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
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

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var SaverInterface */
    protected $productSaver;

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
     * @param ApiResourceRepositoryInterface $mediaRepository
     * @param NormalizerInterface            $normalizer
     * @param ParameterValidatorInterface    $parameterValidator
     * @param PaginatorInterface             $paginator
     * @param FilesystemProvider             $filesystemProvider
     * @param FileFetcherInterface           $fileFetcher
     * @param ProductRepositoryInterface     $productRepository
     * @param ObjectUpdaterInterface         $productUpdater
     * @param SaverInterface                 $productSaver
     * @param ValidatorInterface             $validator
     * @param SaverInterface                 $fileInfoSaver
     * @param FileStorerInterface            $fileStorer
     * @param RemoverInterface               $remover
     * @param RouterInterface                $router
     * @param array                          $apiConfiguration
     */
    public function __construct(
        ApiResourceRepositoryInterface $mediaRepository,
        NormalizerInterface $normalizer,
        ParameterValidatorInterface $parameterValidator,
        PaginatorInterface $paginator,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        ProductRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        SaverInterface $productSaver,
        ValidatorInterface $validator,
        SaverInterface $fileInfoSaver,
        FileStorerInterface $fileStorer,
        RemoverInterface $remover,
        RouterInterface $router,
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
        if (null === $media) {
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
        if (!$request->request->has('product')) {
            throw new UnprocessableEntityHttpException('Property "product" is required.');
        }

        $productInfos = $this->getDecodedContent($request->request->get('product'));
        $product = $this->productRepository->findOneByIdentifier($productInfos['identifier']);
        if (null === $product) {
            throw new UnprocessableEntityHttpException(
                sprintf('Product "%s" does not exist.', $productInfos['identifier'])
            );
        }

        $fileInfo = $this->storeFile($request->files);
        $this->linkFileToProduct($fileInfo, $product, $productInfos);

        $response = new Response(null, Response::HTTP_CREATED);
        $route = $this->router->generate('pim_api_media_file_get', ['code' => $fileInfo->getKey()], true);
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
    protected function linkFileToProduct(FileInfoInterface $fileInfo, ProductInterface $product, array $productInfos)
    {
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
    protected function getDecodedContent($content)
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
}
