<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ApplyProductSearchQueryParametersToPQB
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $channelRepository;

    public function __construct(
        IdentifiableObjectRepositoryInterface $channelRepository
    ) {
        $this->channelRepository = $channelRepository;
    }

    /**
     * Set the PQB filters.
     * If a scope is requested, add a filter to return only products linked to its category tree
     *
     * @param ProductQueryBuilderInterface $pqb
     * @param array $search
     * @param string|null $channelCode
     * @param string|null $searchLocaleCode
     * @param string|null $searchChannelCode
     *
     * @throws UnprocessableEntityHttpException
     */
    public function apply(
        ProductQueryBuilderInterface $pqb,
        array $search,
        ?string $channelCode,
        ?string $searchLocaleCode,
        ?string $searchChannelCode
    ): void {
        $searchParameters = $search;

        if (!isset($searchParameters['categories'])) {
            $channel = $this->channelRepository->findOneByIdentifier($channelCode);
            if (null !== $channel) {
                $searchParameters['categories'] = [
                    [
                        'operator' => Operators::IN_CHILDREN_LIST,
                        'value'    => [$channel->getCategory()->getCode()]
                    ]
                ];
            }
        }

        foreach ($searchParameters as $propertyCode => $filters) {
            $propertyCode = (string) $propertyCode;
            foreach ($filters as $filter) {
                $context['locale'] = $filter['locale'] ?? $searchLocaleCode;

                if (isset($filter['locales']) && '' !== $filter['locales']) {
                    $context['locales'] = $filter['locales'];
                }

                $context['scope'] = $filter['scope'] ?? $searchChannelCode;

                $value = $filter['value'] ?? null;

                if (in_array($propertyCode, ['created', 'updated'])) {
                    if (Operators::BETWEEN === $filter['operator'] && is_array($value)) {
                        $values = [];
                        foreach ($value as $date) {
                            $values[] = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
                        }
                        $value = $values;
                    } elseif (!in_array($filter['operator'], [Operators::SINCE_LAST_N_DAYS, Operators::SINCE_LAST_JOB])) {
                        //PIM-7541 Create the date with the server timezone configuration. Do not force it to UTC timezone.
                        $value = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                    }
                }

                /**
                 * Today, external API uses the "product_index" to search the products. This index indexes parent data
                 * with help of 'ancestors.codes' and 'ancestors.ids'.
                 * To avoid a big refactoring (i.e. use the product_and_product_model_index, TIP-1150), we consider to change the
                 * parent filter to a dedicated one `AncestorCodeFilter`.
                 *
                 * @see src/Akeneo/Pim/Enrichment/Bundle/Elasticsearch/Filter/Field/AncestorCodeFilter.php
                 */
                if ($propertyCode === 'parent') {
                    $pqb->addfilter('ancestor.code', $filter['operator'], $value, $context);
                } else {
                    $pqb->addFilter($propertyCode, $filter['operator'], $value, $context);
                }
            }
        }
    }
}
