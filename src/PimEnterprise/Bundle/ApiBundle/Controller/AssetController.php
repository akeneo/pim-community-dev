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

use Akeneo\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\ApiBundle\Documentation;
use Pim\Component\Api\Exception\DocumentedHttpException;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Api\Pagination\PaginationTypes;
use Pim\Component\Api\Pagination\PaginatorInterface;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Api\Repository\PageableRepositoryInterface;
use Pim\Component\Api\Repository\SearchAfterPageableRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\Security\Exception\ResourceAccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class AssetController
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $identifiableRepository;

    /** @var SearchAfterPageableRepositoryInterface */
    protected $searchAfterRepository;

    /** @var PageableRepositoryInterface */
    protected $offsetRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var PaginatorInterface */
    protected $offsetPaginator;

    /** @var PaginatorInterface */
    protected $searchAfterPaginator;

    /** @var SimpleFactoryInterface */
    protected $factory;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var SaverInterface */
    protected $saver;

    /** @var RouterInterface */
    protected $router;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param IdentifiableObjectRepositoryInterface  $identifiableRepository
     * @param SearchAfterPageableRepositoryInterface $searchAfterRepository
     * @param PageableRepositoryInterface            $offsetRepository
     * @param NormalizerInterface                    $normalizer
     * @param ParameterValidatorInterface            $parameterValidator
     * @param PaginatorInterface                     $offsetPaginator
     * @param PaginatorInterface                     $searchAfterPaginator
     * @param SimpleFactoryInterface                 $factory
     * @param ValidatorInterface                     $validator
     * @param ObjectUpdaterInterface                 $updater
     * @param SaverInterface                         $saver
     * @param RouterInterface                        $router
     * @param array                                  $apiConfiguration
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $identifiableRepository,
        SearchAfterPageableRepositoryInterface $searchAfterRepository,
        PageableRepositoryInterface $offsetRepository,
        NormalizerInterface $normalizer,
        ParameterValidatorInterface $parameterValidator,
        PaginatorInterface $offsetPaginator,
        PaginatorInterface $searchAfterPaginator,
        SimpleFactoryInterface $factory,
        ValidatorInterface $validator,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        RouterInterface $router,
        array $apiConfiguration
    ) {
        $this->identifiableRepository = $identifiableRepository;
        $this->searchAfterRepository = $searchAfterRepository;
        $this->offsetRepository = $offsetRepository;
        $this->normalizer = $normalizer;
        $this->parameterValidator = $parameterValidator;
        $this->offsetPaginator = $offsetPaginator;
        $this->searchAfterPaginator = $searchAfterPaginator;
        $this->factory = $factory;
        $this->validator = $validator;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->router = $router;
        $this->apiConfiguration = $apiConfiguration;
    }

    /**
     * @param string $code
     *
     * @throws NotFoundHttpException         If the asset does not exist
     * @throws ResourceAccessDeniedException If the user don't even have view permissions on the asset
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_list")
     */
    public function getAction(string $code): Response
    {
        $asset = $this->identifiableRepository->findOneByIdentifier($code);

        if (null === $asset) {
            throw new NotFoundHttpException(sprintf('Asset "%s" does not exist.', $code));
        }

        $normalizedAsset = $this->normalizer->normalize($asset, 'external_api');

        return new JsonResponse($normalizedAsset);
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws UnprocessableEntityHttpException
     *
     * @AclAncestor("pim_api_asset_list")
     */
    public function listAction(Request $request): Response
    {
        try {
            $this->parameterValidator->validate($request->query->all(), ['support_search_after' => true]);
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $defaultParameters = [
            'pagination_type' => PaginationTypes::OFFSET,
            'limit'           => $this->apiConfiguration['pagination']['limit_by_default'],
        ];

        $queryParameters = array_merge($defaultParameters, $request->query->all());

        $paginatedAssets = PaginationTypes::OFFSET === $queryParameters['pagination_type'] ?
            $this->searchAfterOffset($queryParameters) :
            $this->searchAfterIdentifier($queryParameters);

        return new JsonResponse($paginatedAssets);
    }

    /**
     * @param Request $request
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_edit")
     */
    public function createAction(Request $request): Response
    {
        $data = $this->getDecodedContent($request->getContent());

        $asset = $this->factory->create();
        $this->updateAsset($asset, $data, 'post_asset');
        $this->validateAsset($asset);

        $this->saver->save($asset);

        $response = $this->getResponse($asset, Response::HTTP_CREATED);

        return $response;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws BadRequestHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_edit")
     */
    public function partialUpdateAction(Request $request, string $code): Response
    {
        $data = $this->getDecodedContent($request->getContent());

        $asset = $this->identifiableRepository->findOneByIdentifier($code);
        $isCreation = null === $asset;

        if ($isCreation) {
            $asset = $this->factory->create();
            $this->validateCodeConsistency($code, $data);
            $data['code'] = $code;
        }
        $this->updateAsset($asset, $data, 'post_asset');
        $this->validateAsset($asset);

        $this->saver->save($asset);

        $status = $isCreation ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;
        $response = $this->getResponse($asset, $status);

        return $response;
    }

    /**
     * @param string $content content of a request to decode
     *
     * @throws BadRequestHttpException
     *
     * @return array
     */
    protected function getDecodedContent(string $content): array
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    /**
     * @param AssetInterface $asset
     * @param integer        $status
     *
     * @return Response
     */
    protected function getResponse(AssetInterface $asset, int $status): Response
    {
        $response = new Response(null, $status);
        $route = $this->router->generate(
            'pimee_api_asset_get',
            ['code' => $asset->getCode()],
            Router::ABSOLUTE_URL
        );

        $response->headers->set('Location', $route);

        return $response;
    }

    /**
     * @param AssetInterface $asset
     * @param array          $data
     * @param string         $anchor
     *
     * @throws DocumentedHttpException
     */
    protected function updateAsset(AssetInterface $asset, array $data, string $anchor)
    {
        try {
            $this->updater->update($asset, $data);
        } catch (PropertyException $exception) {
            throw new DocumentedHttpException(
                Documentation::URL . $anchor,
                sprintf('%s Check the expected format on the API documentation.', $exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * @param AssetInterface $asset
     *
     * @throws ViolationHttpException
     */
    protected function validateAsset(AssetInterface $asset)
    {
        $violations = $this->validator->validate($asset);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }

    /**
     * @param array $queryParameters
     *
     * @return array
     */
    protected function searchAfterOffset(array $queryParameters): array
    {
        $queryParameters = array_merge(['page' => 1, 'with_count' => 'false'], $queryParameters);
        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);

        $paginationParameters = [
            'query_parameters'    => $queryParameters,
            'list_route_name'     => 'pimee_api_asset_list',
            'item_route_name'     => 'pimee_api_asset_get',
            'item_identifier_key' => 'code',
        ];

        $count = 'true' === $queryParameters['with_count'] ? $this->offsetRepository->count() : null;
        $assets = $this->offsetRepository->searchAfterOffset([], ['code' => 'ASC'], $queryParameters['limit'], $offset);
        $normalizedAssets = $this->normalizer->normalize($assets, 'external_api');
        $paginatedAssets = $this->offsetPaginator->paginate(
            $normalizedAssets,
            $paginationParameters,
            $count
        );

        return $paginatedAssets;
    }

    /**
     * @param array $queryParameters
     *
     * @return array
     */
    protected function searchAfterIdentifier(array $queryParameters): array
    {
        $from = isset($queryParameters['search_after']) ? ['code' => $queryParameters['search_after']] : [];

        $assets = $this->searchAfterRepository->searchAfterIdentifier([], ['code' => 'ASC'], (int) $queryParameters['limit'], $from);
        $normalizedAssets = $this->normalizer->normalize($assets, 'external_api');

        $lastAsset = end($assets);
        reset($assets);

        $paginationParameters = [
            'query_parameters' => $queryParameters,
            'search_after'     => [
                'next' => false !== $lastAsset ? $lastAsset->getCode() : null,
                'self' => $queryParameters['search_after'] ?? null,
            ],
            'list_route_name'  => 'pimee_api_asset_list',
            'item_route_name'  => 'pimee_api_asset_get',
        ];

        $paginatedAssets = $this->searchAfterPaginator->paginate(
            $normalizedAssets,
            $paginationParameters,
            null
        );

        return $paginatedAssets;
    }

    /**
     * @param string $code code provided in the url
     * @param array  $data body of the request already decoded
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function validateCodeConsistency(string $code, array $data): void
    {
        if (isset($data['code']) && $code !== $data['code']) {
            throw new UnprocessableEntityHttpException(
                sprintf(
                    'The code "%s" provided in the request body must match the code "%s" provided in the url.',
                    $data['code'],
                    $code
                )
            );
        }
    }
}
