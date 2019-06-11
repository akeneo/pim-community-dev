<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * This class filters data with the ancestor code, i.e. the parent code. We cannot use ParentFilter as it this
 * field is unfortunately not in product index. It is only in product and product model index.
 * This is temporary and is used into external API product list, and will be removed after TIP-1150.
 *
 * @see src/Akeneo/Pim/Enrichment/Component/Product/Connector/UseCase/ApplyProductSearchQueryParametersToPQB.php
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AncestorCodeFilter extends AbstractFieldFilter
{
    private const ANCESTOR_CODES_ES_FIELD = 'ancestors.codes';

    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param array                           $supportedFields
     * @param array                           $supportedOperators
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        array $supportedFields,
        array $supportedOperators
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = []): void
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        FieldFilterHelper::checkIdentifier($field, $value, static::class);
        $clause = [
            'terms' => [
                self::ANCESTOR_CODES_ES_FIELD => [$value],
            ],
        ];

        $this->searchQueryBuilder->addFilter($clause);
    }
}
