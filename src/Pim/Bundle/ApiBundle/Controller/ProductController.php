<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Pagination\HalPaginator;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
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
class ProductController
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var HalPaginator */
    protected $paginator;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var RemoverInterface */
    protected $remover;

    /**
     * @param ProductQueryBuilderFactoryInterface   $pqbFactory
     * @param NormalizerInterface                   $normalizer
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param ProductRepositoryInterface            $productRepository
     * @param HalPaginator                          $paginator
     * @param ParameterValidatorInterface           $parameterValidator
     * @param RemoverInterface                      $remover
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        NormalizerInterface $normalizer,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        ProductRepositoryInterface $productRepository,
        HalPaginator $paginator,
        ParameterValidatorInterface $parameterValidator,
        RemoverInterface $remover
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
        $this->paginator = $paginator;
        $this->parameterValidator = $parameterValidator;
        $this->remover = $remover;
    }

    /**
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $queryParameters = [];
        $queryParameters['page'] = $request->query->get('page', 1);
        $queryParameters['limit'] = $request->query->get('limit', 10); // limit will be put in config in an other PR

        try {
            $this->parameterValidator->validate($queryParameters);
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $normalizerOptions = [];
        $channel = null;

        if ($request->query->has('channel')) {
            $channel = $this->channelRepository->findOneByIdentifier($request->query->get('channel'));
            if (null === $channel) {
                throw new UnprocessableEntityHttpException(
                    sprintf('Channel "%s" does not exist.', $request->query->get('channel'))
                );
            }

            $normalizerOptions['channels'] = [$channel->getCode()];
            $normalizerOptions['locales'] = $channel->getLocaleCodes();
        }

        if ($request->query->has('locales')) {
            $this->checkLocalesParameters($request->query->get('locales'), $channel);

            $normalizerOptions['locales'] = explode(',', $request->query->get('locales'));
        }

        if ($request->query->has('attributes')) {
            $this->checkAttributesParameters($request->query->get('attributes'));

            $normalizerOptions['attributes'] = explode(',', $request->query->get('attributes'));
        }

        $pqb = $this->pqbFactory->create([]);
        // TODO: be able to use the PQB filters (will be done in API-65).
        // for the moment, set an empty array
        $this->setPQBFilters($pqb, [], $channel);

        $count = $this->productRepository->count($pqb->getQueryBuilder());

        $pqb->getQueryBuilder()
            ->setMaxResults($queryParameters['limit'])
            ->setFirstResult(($queryParameters['page'] - 1) * $queryParameters['limit']);

        $standardProducts = $this->normalizer->normalize($pqb->execute(), 'external_api', $normalizerOptions);
        $paginatedProducts = $this->paginator->paginate(
            $standardProducts,
            array_merge($request->query->all(), $queryParameters),
            $count,
            'pim_api_product_list',
            'pim_api_product_get',
            'identifier'
        );

        return new JsonResponse($paginatedProducts);
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
        $product = $this->productRepository->findOneByIdentifier($code);
        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist.', $code));
        }

        $standardizedProduct = $this->normalizer->normalize($product, 'external_api');

        return new JsonResponse($standardizedProduct);
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $code)
    {
        $product = $this->productRepository->findOneByIdentifier($code);
        if (null === $product) {
            throw new NotFoundHttpException(sprintf('Product "%s" does not exist.', $code));
        }

        $this->remover->remove($product);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Set the PQB filters.
     * If a channel is requested, add a filter to return only products linked to its category tree
     *
     * @param ProductQueryBuilderInterface $pqb
     * @param array                        $pqbFilters
     * @param ChannelInterface|null        $channel
     */
    protected function setPQBFilters(
        ProductQueryBuilderInterface $pqb,
        array $pqbFilters,
        ChannelInterface $channel = null
    ) {
        if (null !== $channel) {
            $pqbFilters['categories'] = [
                [
                    'operator' => Operators::IN_CHILDREN_LIST,
                    'value'    => [$channel->getCategory()->getCode()]
                ]
            ];
        }

        foreach ($pqbFilters as $attributeCode => $filters) {
            foreach ($filters as $filter) {
                $pqb->addFilter($attributeCode, $filter['operator'], $filter['value']);
            }
        }
    }

    /**
     * Checks $localeCodes if they exist.
     * Thrown an exception if one of them does not exist or, if there is a $channel, one of them does not belong to it.
     *
     * @param string                $localeCodes
     * @param ChannelInterface|null $channel
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function checkLocalesParameters($localeCodes, ChannelInterface $channel = null)
    {
        $locales = explode(',', $localeCodes);

        $errors = [];
        foreach ($locales as $locale) {
            if (null === $this->localeRepository->findOneByIdentifier($locale)) {
                $errors[] = $locale;
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ? 'Locales "%s" do not exist.' : 'Locale "%s" does not exist.';
            throw new UnprocessableEntityHttpException(sprintf($plural, implode(', ', $errors)));
        }

        if (null !== $channel) {
            if ($diff = array_diff($locales, $channel->getLocaleCodes())) {
                $plural = sprintf(count($diff) > 1 ? 'Locales "%s" are' : 'Locale "%s" is', implode(', ', $diff));
                throw new UnprocessableEntityHttpException(
                    sprintf('%s not activated for the channel "%s".', $plural, $channel->getCode())
                );
            }
        }
    }

    /**
     * Checks $attributes if they exist. Thrown an exception if one of them does not exist.
     *
     * @param string $attributes
     *
     * @throws UnprocessableEntityHttpException
     */
    protected function checkAttributesParameters($attributes)
    {
        $attributeCodes = explode(',', $attributes);

        $errors = [];
        foreach ($attributeCodes as $attributeCode) {
            if (null === $this->attributeRepository->findOneByIdentifier($attributeCode)) {
                $errors[] = $attributeCode;
            }
        }

        if (!empty($errors)) {
            $plural = count($errors) > 1 ? 'Attributes "%s" do not exist.' : 'Attribute "%s" does not exist.';
            throw new UnprocessableEntityHttpException(sprintf($plural, implode(', ', $errors)));
        }
    }
}
