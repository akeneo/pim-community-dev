<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Pagination\HalPaginator;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class LocaleController
{
    /** @var LocaleRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var HalPaginator */
    protected $paginator;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /**
     * @param LocaleRepositoryInterface  $repository
     * @param NormalizerInterface         $normalizer
     * @param HalPaginator                $paginator
     * @param ParameterValidatorInterface $parameterValidator
     */
    public function __construct(
        LocaleRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        HalPaginator $paginator,
        ParameterValidatorInterface $parameterValidator
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
        $this->paginator = $paginator;
        $this->parameterValidator = $parameterValidator;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $code)
    {
        $locale = $this->repository->findOneByIdentifier($code);
        if (null === $locale) {
            throw new NotFoundHttpException(sprintf('Locale "%s" does not exist.', $code));
        }

        $localeApi = $this->normalizer->normalize($locale, 'external_api');

        return new JsonResponse($localeApi);
    }

    /**
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     * @return JsonResponse
     *
     */
    public function listAction(Request $request)
    {
        $queryParameters = [];
        $queryParameters['page'] = $request->query->get('page', 1);
        $queryParameters['limit'] = $request->query->get('limit', 10);

        try {
            $this->parameterValidator->validate($queryParameters);
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }
        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);

        $count = $this->repository->countAll();
        $locales = $this->repository->findBy([], ['code' => 'ASC'], $queryParameters['limit'], $offset);

        $localesApi = $this->normalizer->normalize($locales, 'external_api');

        $paginatedLocales = $this->paginator->paginate(
            $localesApi,
            array_merge($request->query->all(), $queryParameters),
            $count,
            'pim_api_rest_locale_list',
            'pim_api_rest_locale_get',
            'code'
        );

        return new JsonResponse($paginatedLocales);
    }
}
