<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Akeneo\Component\FileStorage\FilesystemProvider;
use Akeneo\Component\FileStorage\StreamedFileResponse;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Pagination\HalPaginator;
use Pim\Component\Api\Pagination\ParameterValidator;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ApiResourceRepositoryInterface */
    protected $mediaRepository;

    /** @var ParameterValidator */
    protected $parameterValidator;

    /** @var HalPaginator */
    protected $paginator;

    /** @var FilesystemProvider */
    protected $filesystemProvider;

    /** @var array */
    protected $apiConfiguration;

    /** @var array */
    protected $filesystemAliases;

    /** @var string */
    protected $urlDocumentation;

    /**
     * @param ApiResourceRepositoryInterface $mediaRepository
     * @param NormalizerInterface            $normalizer
     * @param ParameterValidator             $parameterValidator
     * @param HalPaginator                   $paginator
     * @param FilesystemProvider             $filesystemProvider
     * @param array                          $filesystemAliases
     * @param array                          $apiConfiguration
     * @param string                         $urlDocumentation
     */
    public function __construct(
        ApiResourceRepositoryInterface $mediaRepository,
        NormalizerInterface $normalizer,
        ParameterValidator $parameterValidator,
        HalPaginator $paginator,
        FilesystemProvider $filesystemProvider,
        array $filesystemAliases,
        array $apiConfiguration,
        $urlDocumentation
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->normalizer = $normalizer;
        $this->parameterValidator = $parameterValidator;
        $this->paginator = $paginator;
        $this->filesystemProvider = $filesystemProvider;
        $this->filesystemAliases = $filesystemAliases;
        $this->apiConfiguration = $apiConfiguration;
        $this->urlDocumentation = $urlDocumentation;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $code)
    {
        $media = $this->mediaRepository->findOneByIdentifier($code);
        if (null === $media) {
            throw new NotFoundHttpException(sprintf('Media file "%s" does not exist.', $code));
        }

        $mediaApi = $this->normalizer->normalize($media, 'external_api');

        return new JsonResponse($mediaApi);
    }

    /**
     * @param Request $request
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
        $medias = $this->mediaRepository->searchAfterOffset([], [], $queryParameters['limit'], $offset);

        $paginatedMedias = $this->paginator->paginate(
            $this->normalizer->normalize($medias, 'external_api'),
            array_merge($request->query->all(), $queryParameters),
            $this->mediaRepository->count(),
            'pim_api_media_file_list',
            'pim_api_media_file_get',
            'code'
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

        foreach ($this->filesystemAliases as $alias) {
            $fs = $this->filesystemProvider->getFilesystem($alias);
            if ($fs->has($filename)) {
                $stream = $fs->readStream($filename);
                $headers = [];

                if (null !== $fileInfo = $this->mediaRepository->findOneByIdentifier($filename)) {
                    $headers['Content-Disposition'] = sprintf(
                        'attachment; filename="%s"',
                        $fileInfo->getOriginalFilename()
                    );
                }

                return new StreamedFileResponse($stream, Response::HTTP_OK, $headers);
            }
        }

        throw new NotFoundHttpException(sprintf('Media file "%s" does not exist.', $filename));
    }
}
