<?php

namespace spec\Pim\Bundle\ImportExportBundle\Doctrine\Commun;

use Doctrine\ORM\Query as ORMQuery;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\ImportExportBundle\Doctrine\Commun\AbstractProductSearchableRepository;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class AbstractProductSearchableRepositorySpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beAnInstanceOf('spec\Pim\Bundle\ImportExportBundle\Doctrine\Commun\ConcreteClass');
        $this->beConstructedWith($productQueryBuilderFactory, $attributeRepository);
    }
    
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ImportExportBundle\Doctrine\Commun\AbstractProductSearchableRepository');
    }

    function it_is_a_searchable_repository()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface');
    }
}

class ConcreteClass extends AbstractProductSearchableRepository {
    protected function buildQuery(
        ProductQueryBuilderInterface $productQueryBuilder,
        $search,
        array $options
    ) {}
}
