<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilder;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductMassActionRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em)
    {
        $name = Product::class;
        $this->beConstructedWith($em, $name);
    }

    function it_throws_an_exception_when_trying_to_delete_without_product_ids()
    {
        $this->shouldThrow(new \LogicException('No products to remove'))->duringDeleteFromIds(array());
    }

    function it_applies_mass_action_parameters_with_product_models_to_exclude(ProductQueryBuilder $queryBuilder)
    {
        $queryBuilder->addFilter('id', Operators::NOT_IN_LIST, ['product_1', 'product_model_3'])->shouldBeCalled();
        $queryBuilder->addFilter('ancestor.id', Operators::NOT_IN_LIST, ['product_model_3'])->shouldBeCalled();

        $this->applyMassActionParameters($queryBuilder, false, ['product_1', 'product_model_3']);
    }

    function it_applies_mass_action_parameters_without_product_models_to_exclude(ProductQueryBuilder $queryBuilder)
    {
        $queryBuilder->addFilter('id', Operators::NOT_IN_LIST, ['product_1', 'product_3'])->shouldBeCalled();
        $queryBuilder->addFilter('ancestor.id', Argument::cetera())->shouldNotBeCalled();

        $this->applyMassActionParameters($queryBuilder, false, ['product_1', 'product_3']);
    }
}
