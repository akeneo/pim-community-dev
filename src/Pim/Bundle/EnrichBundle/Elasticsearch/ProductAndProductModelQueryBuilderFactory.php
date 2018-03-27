<?php

namespace Pim\Bundle\EnrichBundle\Elasticsearch;

use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

/**
 * Aims to wrap the creation and configuration of the product and product model query builder
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelQueryBuilderFactory implements ProductQueryBuilderFactoryInterface
{
    /** @var string */
    private $pqbClass;

    /** @var ProductQueryBuilderFactoryInterface */
    private $factory;

    /**
     * @param string                              $pqbClass
     * @param ProductQueryBuilderFactoryInterface $factory
     */
    public function __construct(string $pqbClass, ProductQueryBuilderFactoryInterface $factory)
    {
        $this->pqbClass = $pqbClass;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = []): ProductQueryBuilderInterface
    {
        $basePqb = $this->factory->create($options);

        return new $this->pqbClass($basePqb);
    }
}
