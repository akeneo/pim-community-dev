<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\File\FileFetcherInterface;
use Akeneo\Component\FileStorage\FilesystemProvider;
use Akeneo\Component\FileStorage\StreamedFileResponse;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Pagination\HalPaginator;
use Pim\Component\Api\Pagination\ParameterValidator;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
use Pim\Component\Catalog\FileStorage;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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

    /** @var ParameterValidator */
    protected $parameterValidator;

    /** @var HalPaginator */
    protected $paginator;

    /** @var FilesystemProvider */
    protected $filesystemProvider;

    /** @var FileFetcherInterface */
    protected $fileFetcher;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param ApiResourceRepositoryInterface $mediaRepository
     * @param NormalizerInterface            $normalizer
     * @param ParameterValidator             $parameterValidator
     * @param HalPaginator                   $paginator
     * @param FilesystemProvider             $filesystemProvider
     * @param FileFetcherInterface           $fileFetcher
     * @param array                          $apiConfiguration
     */
    public function __construct(
        ApiResourceRepositoryInterface $mediaRepository,
        NormalizerInterface $normalizer,
        ParameterValidator $parameterValidator,
        HalPaginator $paginator,
        FilesystemProvider $filesystemProvider,
        FileFetcherInterface $fileFetcher,
        array $apiConfiguration
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->normalizer = $normalizer;
        $this->parameterValidator = $parameterValidator;
        $this->paginator = $paginator;
        $this->filesystemProvider = $filesystemProvider;
        $this->apiConfiguration = $apiConfiguration;
        $this->fileFetcher = $fileFetcher;
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
        $queryParameters = [
            'page'  => $request->query->get('page', 1),
            'limit' => $request->query->get('limit', $this->apiConfiguration['pagination']['limit_by_default'])
        ];

        try {
            $this->parameterValidator->validate($queryParameters);
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);
        $criteria = ['storage' => FileStorage::CATALOG_STORAGE_ALIAS];
        $medias = $this->mediaRepository->searchAfterOffset($criteria, [], $queryParameters['limit'], $offset);

        $parameters = [
            'query_parameters' => array_merge($request->query->all(), $queryParameters),
            'list_route_name'  => 'pim_api_media_file_list',
            'item_route_name'  => 'pim_api_media_file_get'
        ];

        $paginatedMedias = $this->paginator->paginate(
            $this->normalizer->normalize($medias, 'external_api'),
            $parameters,
            $this->mediaRepository->count($criteria)
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
}
