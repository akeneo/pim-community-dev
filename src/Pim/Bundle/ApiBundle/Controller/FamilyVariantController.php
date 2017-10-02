<?php

declare(strict_types=1);

namespace Pim\Bundle\ApiBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Pagination\PaginatorInterface;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantController
{
    /** @var ApiResourceRepositoryInterface */
    protected $familyRepository;

    /** @var ApiResourceRepositoryInterface */
    protected $familyVariantRepository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var PaginatorInterface */
    protected $paginator;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var array */
    protected $apiConfiguration;

    /**
     * @param ApiResourceRepositoryInterface $familyRepository
     * @param ApiResourceRepositoryInterface $familyVariantRepository
     * @param NormalizerInterface            $normalizer
     * @param PaginatorInterface             $paginator
     * @param ParameterValidatorInterface    $parameterValidator
     * @param array                          $apiConfiguration
     */
    public function __construct(
        ApiResourceRepositoryInterface $familyRepository,
        ApiResourceRepositoryInterface $familyVariantRepository,
        NormalizerInterface $normalizer,
        PaginatorInterface $paginator,
        ParameterValidatorInterface $parameterValidator,
        array $apiConfiguration
    ) {
        $this->familyRepository = $familyRepository;
        $this->familyVariantRepository = $familyVariantRepository;
        $this->normalizer = $normalizer;
        $this->paginator = $paginator;
        $this->parameterValidator = $parameterValidator;
        $this->apiConfiguration = $apiConfiguration;
    }

    /**
     * @param Request $request
     * @param string  $familyCode
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_api_family_variant_list")
     */
    public function getAction(Request $request, string $familyCode, string $code)
    {
        $family = $this->familyRepository->findOneByIdentifier($familyCode);
        if (null === $family) {
            throw new NotFoundHttpException(sprintf('Family "%s" does not exist.', $familyCode));
        }

        $familyVariant = $this->familyVariantRepository->findOneByIdentifier($code);
        if (null === $familyVariant || $familyVariant->getFamily()->getCode() !== $familyCode) {
            throw new NotFoundHttpException(
                sprintf(
                    'Family variant "%s" does not exist or is not a variant of the family "%s".',
                    $code,
                    $familyCode
                )
            );
        }

        $familyVariantApi = $this->normalizer->normalize($familyVariant, 'external_api');

        return new JsonResponse($familyVariantApi);
    }

    /**
     * @param Request $request
     * @param string  $familyCode
     *
     * @throws NotFoundHttpException
     * @throws UnprocessableEntityHttpException
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_api_family_variant_list")
     */
    public function listAction(Request $request, string $familyCode)
    {
        $family = $this->familyRepository->findOneByIdentifier($familyCode);
        if (null === $family) {
            throw new NotFoundHttpException(sprintf('Family "%s" does not exist.', $familyCode));
        }

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

        $criteria['family'] = $family->getId();

        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);
        $familyVariants = $this->familyVariantRepository->searchAfterOffset($criteria, ['code' =>'ASC'], $queryParameters['limit'], $offset);

        $parameters = [
            'query_parameters'    => $queryParameters,
            'uri_parameters'      => ['familyCode' => $familyCode],
            'list_route_name'     => 'pim_api_family_variant_list',
            'item_route_name'     => 'pim_api_family_variant_get',
        ];

        $count = true === $request->query->getBoolean('with_count') ? $this->familyVariantRepository->count($criteria) : null;
        $paginatedFamilies = $this->paginator->paginate(
            $this->normalizer->normalize($familyVariants, 'external_api'),
            $parameters,
            $count
        );

        return new JsonResponse($paginatedFamilies);
    }
}
