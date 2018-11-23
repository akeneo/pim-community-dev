<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Grid\Query;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;

/**
 * Ideally, we should inject a Product Query with all the filters and not a query builder.
 * Then, this query could be executed with the service of our choice (ES, Mysql, fake).
 *
 * But the current implementation of the query builder directly
 * contains the filters and has the responsibility of executing the query.
 *
 * We have to stick with this behavior for now.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FetchProductAndProductModelRowsParameters
{
    /** @var ProductQueryBuilderInterface */
    private $productQueryBuilder;

    /** @var array */
    private $attributeCodes;

    /** @var string */
    private $channelCode;

    /** @var string */
    private $localeCode;

    /**
     * @param ProductQueryBuilderInterface $productQueryBuilder
     * @param array                        $attributes
     * @param string                       $channel
     * @param string                       $locale
     */
    public function __construct(
        ProductQueryBuilderInterface $productQueryBuilder,
        array $attributes,
        string $channel,
        string $locale
    ) {
        $this->productQueryBuilder = $productQueryBuilder;
        $this->attributeCodes = $attributes;
        $this->channelCode = $channel;
        $this->localeCode = $locale;
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    public function productQueryBuilder(): ProductQueryBuilderInterface
    {
        return $this->productQueryBuilder;
    }

    /**
     * @return array
     */
    public function attributeCodes(): array
    {
        return $this->attributeCodes;
    }

    /**
     * @return string
     */
    public function channelCode(): string
    {
        return $this->channelCode;
    }

    /**
     * @return string
     */
    public function localeCode(): string
    {
        return $this->localeCode;
    }
}
