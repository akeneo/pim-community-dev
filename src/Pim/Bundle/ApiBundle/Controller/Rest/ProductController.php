<?php

namespace Pim\Bundle\ApiBundle\Controller\Rest;

use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param NormalizerInterface                 $normalizer
     * @param ChannelRepositoryInterface          $channelRepository
     * @param LocaleRepositoryInterface           $localeRepository
     * @param AttributeRepositoryInterface        $attributeRepository
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        NormalizerInterface $normalizer,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->normalizer = $normalizer;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->attributeRepository = $attributeRepository;
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
        $normalizerOptions = [];
        $pqbFilters = [];
        $channel = null;

        if ($request->query->has('channel')) {
            $channelCode = $request->query->get('channel');
            if (null === $channel = $this->channelRepository->findOneByIdentifier($channelCode)) {
                throw new UnprocessableEntityHttpException(sprintf('Channel "%s" does not exist.', $channelCode));
            }

            $normalizerOptions['channels'] = [$channelCode];
            $normalizerOptions['locales'] = $channel->getLocaleCodes();
            $pqbFilters['categories.code'] = [
                [
                    'operator' => Operators::IN_CHILDREN_LIST,
                    'value'    => [$channel->getCategory()->getCode()]
                ]
            ];
        }

        if ($request->query->has('locales')) {
            $locales = explode(',', $request->query->get('locales'));

            foreach ($locales as $locale) {
                if (null === $this->localeRepository->findOneByIdentifier($locales)) {
                    throw new UnprocessableEntityHttpException(sprintf('Locale "%s" does not exist.', $locale));
                }
            }

            $normalizerOptions['locales'] = $locales;

            if (null !== $channel) {
                if ($diff = array_diff($locales, $channel->getLocaleCodes())) {
                    $plural = sprintf(count($diff) > 1 ? 'Locales "%s" are' : 'Locale "%s" is', implode(', ', $diff));
                    throw new UnprocessableEntityHttpException(
                        sprintf('%s not activated for the channel "%s".', $plural, $channelCode)
                    );
                }
            }
        }

        if ($request->query->has('attributes')) {
            $attributeCodes = explode(',', $request->query->get('attributes'));

            $diff = [];
            foreach ($attributeCodes as $attributeCode) {
                if (null === $this->attributeRepository->findOneByIdentifier($attributeCode)) {
                    $diff[] = $attributeCode;
                }
            }

            if (!empty($diff)) {
                $plural = count($diff) > 1 ? 'Attributes "%s" do not exist ' : 'Attribute "%s" does not exist';
                throw new UnprocessableEntityHttpException(sprintf($plural, implode(', ', $diff)));
            }

            $normalizerOptions['attributes'] = $attributeCodes;
        }

        $pqb = $this->pqbFactory->create([]);

        foreach ($pqbFilters as $attributeCode => $filters) {
            foreach ($filters as $filter) {
                $pqb->addFilter($attributeCode, $filter['operator'], $filter['value']);
            }
        }

        $pqb->getQueryBuilder()->setMaxResults($request->query->getInt('limit', 10)); // remove 10 and set it in config
        $productStandard = $this->normalizer->normalize($pqb->execute(), 'external_api', $normalizerOptions);

        return new JsonResponse($productStandard);
    }
}
