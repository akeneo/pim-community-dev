<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PimEnterprise\Bundle\ApiBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Pagination\PaginatorInterface;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 */
class AssetCategoryController
{
    /** @var ApiResourceRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var PaginatorInterface */
    protected $paginator;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param ApiResourceRepositoryInterface $repository
     * @param NormalizerInterface            $normalizer
     * @param ParameterValidatorInterface    $parameterValidator
     * @param PaginatorInterface             $paginator
     * @param array                          $apiConfiguration
     */
    public function __construct(
        ApiResourceRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        ParameterValidatorInterface $parameterValidator,
        PaginatorInterface $paginator,
        array $apiConfiguration
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
        $this->parameterValidator = $parameterValidator;
        $this->paginator = $paginator;
        $this->apiConfiguration = $apiConfiguration;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_category_list")
     */
    public function getAction(Request $request, string $code): Response
    {
        $assetCategory = $this->repository->findOneByIdentifier($code);
        if (null === $assetCategory) {
            throw new NotFoundHttpException(sprintf('Asset category "%s" does not exist.', $code));
        }

        $assetCategoryApi = $this->normalizer->normalize($assetCategory, 'external_api');

        return new JsonResponse($assetCategoryApi);
    }

    /**
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return Response
     *
     * @AclAncestor("pim_api_asset_category_list")
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
        $order = ['root' => 'ASC', 'left' => 'ASC'];
        $assetCategories = $this->repository->searchAfterOffset([], $order, $queryParameters['limit'], $offset);

        $parameters = [
            'query_parameters' => $queryParameters,
            'list_route_name'  => 'pimee_api_category_asset_list',
            'item_route_name'  => 'pimee_api_category_asset_get',
        ];

        $count = true === $request->query->getBoolean('with_count') ? $this->repository->count() : null;
        $paginatedCategories = $this->paginator->paginate(
            $this->normalizer->normalize($assetCategories, 'external_api'),
            $parameters,
            $count
        );

        return new JsonResponse($paginatedCategories);
    }
}
