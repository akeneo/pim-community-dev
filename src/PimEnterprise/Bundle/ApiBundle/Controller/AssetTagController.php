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
use Pim\Bundle\ApiBundle\Documentation;
use Pim\Component\Api\Exception\DocumentedHttpException;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Exception\ViolationHttpException;
use Pim\Component\Api\Pagination\PaginatorInterface;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class AssetTagController
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $assetTagRepository;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var PaginatorInterface */
    protected $paginator;

    /** @var SimpleFactoryInterface */
    protected $factory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var SaverInterface */
    protected $saver;

    /** @var Router */
    protected $router;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param IdentifiableObjectRepositoryInterface $assetTagRepository
     * @param ParameterValidatorInterface           $parameterValidator
     * @param PaginatorInterface                    $paginator
     * @param SimpleFactoryInterface                $factory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     * @param SaverInterface                        $saver
     * @param Router                                $router
     * @param array                                 $apiConfiguration
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $assetTagRepository,
        ParameterValidatorInterface $parameterValidator,
        PaginatorInterface $paginator,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        Router $router,
        array $apiConfiguration
    ) {
        $this->assetTagRepository = $assetTagRepository;
        $this->parameterValidator = $parameterValidator;
        $this->paginator = $paginator;
        $this->factory = $factory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->apiConfiguration = $apiConfiguration;
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @param string $code
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function getAction(Request $request, string $code): Response
    {
        $assetTag = $this->assetTagRepository->findOneByIdentifier($code);

        if (null === $assetTag) {
            throw new NotFoundHttpException(sprintf('Tag "%s" does not exist.', $code));
        }

        return new JsonResponse(['code' => $assetTag->getCode()]);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request): Response
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
        $assetTags = $this->assetTagRepository->searchAfterOffset([], ['code' => 'ASC'], $queryParameters['limit'], $offset);

        $parameters = [
            'query_parameters' => $queryParameters,
            'list_route_name'  => 'pimee_api_asset_tag_list',
            'item_route_name'  => 'pimee_api_asset_tag_get',
        ];

        $assetTagsNormalized = [];
        foreach ($assetTags as $assetTag){
            $assetTagsNormalized[] = ['code' => $assetTag->getCode()];
        }

        $count = true === $request->query->getBoolean('with_count') ? $this->assetTagRepository->count() : null;
        $paginatedAssetTags = $this->paginator->paginate(
            $assetTagsNormalized,
            $parameters,
            $count
        );

        return new JsonResponse($paginatedAssetTags);
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @return Response
     */
    public function partialUpdateAction(Request $request, string $code): Response
    {
        $data = $this->getDecodedContent($request->getContent());

        $isCreation = false;
        $assetTag = $this->assetTagRepository->findOneByIdentifier($code);

        if (null === $assetTag) {
            $isCreation = true;
            $this->validateCodeConsistency($code, $data);
            $data['code'] = $code;
            $assetTag = $this->factory->create();
        }

        $this->updateTag($assetTag, $data, 'patch_asset_tags__code_');
        $this->validateTag($assetTag);

        $this->saver->save($assetTag);

        $status = $isCreation ? Response::HTTP_CREATED : Response::HTTP_NO_CONTENT;
        $response = $this->getResponse($assetTag, $status);

        return $response;
    }

    /**
     * Get the JSON decoded content. If the content is not a valid JSON, it throws an error 400.
     *
     * @param string $content content of a request to decode
     *
     * @throws BadRequestHttpException
     *
     * @return array
     */
    protected function getDecodedContent($content): array
    {
        $decodedContent = json_decode($content, true);

        if (null === $decodedContent) {
            throw new BadRequestHttpException('Invalid json message received');
        }

        return $decodedContent;
    }

    /**
     * Throw an exception if the code provided in the url and the code provided in the request body
     * are not equals when creating a tag with a PATCH method.
     *
     * The code in the request body is optional when we create a resource with PATCH.
     *
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

    /**
     * Update a tag. It throws an error 422 if a problem occurred during the update.
     *
     * @param TagInterface $tag  tag to update
     * @param array        $data data of the request already decoded
     * @param string       $anchor
     *
     * @throws DocumentedHttpException
     */
    protected function updateTag(TagInterface $tag, array $data, $anchor): void
    {
        try {
            $this->updater->update($tag, $data);
        } catch (PropertyException $exception) {
            throw new DocumentedHttpException(
                Documentation::URL . $anchor,
                sprintf('%s Check the expected format on the API documentation.', $exception->getMessage()),
                $exception
            );
        }
    }

    /**
     * Validate a tag. It throws an error 422 with every violated constraints if
     * the validation failed.
     *
     * @param TagInterface $tag
     *
     * @throws ViolationHttpException
     */
    protected function validateTag(TagInterface $tag): void
    {
        $violations = $this->validator->validate($tag);
        if (0 !== $violations->count()) {
            throw new ViolationHttpException($violations);
        }
    }

    /**
     * Get a response with a location header to the created or updated resource.
     *
     * @param TagInterface $tag
     * @param string       $status
     *
     * @return Response
     */
    protected function getResponse(TagInterface $tag, $status)
    {
        $response = new Response(null, $status);
        $route = $this->router->generate(
            'pimee_api_asset_tag_get',
            ['code' => $tag->getCode()],
            Router::ABSOLUTE_URL
        );
        $response->headers->set('Location', $route);

        return $response;
    }
}
