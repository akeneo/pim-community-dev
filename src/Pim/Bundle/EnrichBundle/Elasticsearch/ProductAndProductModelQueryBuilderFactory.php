<?php

namespace Pim\Bundle\EnrichBundle\Elasticsearch;

use Pim\Bundle\CatalogBundle\Elasticsearch\ProductQueryBuilderFactory;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;

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
    protected $pqbClass;

    /** @var ProductQueryBuilderFactory */
    private $factory;

    /**
     * @param string                     $pqbClass
     * @param ProductQueryBuilderFactory $factory
     */
    public function __construct($pqbClass, ProductQueryBuilderFactory $factory)
    {
        $this->pqbClass = $pqbClass;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $options = [])
    {
        $basePqb = $this->factory->create($options);

        return new $this->pqbClass($basePqb);
    }
}
