<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\ProductQueryBuilder;

use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductAndProductModelQueryBuilderFactory implements ProductQueryBuilderFactoryInterface
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
