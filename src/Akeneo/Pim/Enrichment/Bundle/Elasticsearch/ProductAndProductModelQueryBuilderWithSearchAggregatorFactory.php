<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductAndProductModelQueryBuilderWithSearchAggregatorFactory implements ProductQueryBuilderFactoryInterface
{
    /** @var string */
    private $pqbClass;

    /** @var ProductQueryBuilderFactoryInterface */
    private $factory;

    /** @var ProductAndProductModelSearchAggregator */
    private $searchAggregator;

    public function __construct(
        string $pqbClass,
        ProductQueryBuilderFactoryInterface $factory,
        ProductAndProductModelSearchAggregator $searchAggregator = null
    ) {
        $this->pqbClass = $pqbClass;
        $this->factory = $factory;
        $this->searchAggregator = $searchAggregator;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = []): ProductQueryBuilderInterface
    {
        $basePqb = $this->factory->create($options);

        return new $this->pqbClass($basePqb, $this->searchAggregator);
    }
}
