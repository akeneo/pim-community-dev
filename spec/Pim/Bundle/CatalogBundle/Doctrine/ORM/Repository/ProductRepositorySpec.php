<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;
use Prophecy\Argument;

class ProductRepositorySpec extends ObjectBehavior
{
    function let(EntityManager $em, ClassMetadata $class, ConfigurationRegistryInterface $registry)
    {
        $class->name = 'Pim\Bundle\CatalogBundle\Model\Product';
        $this->beConstructedWith($em, $class);
        $this->setReferenceDataRegistry($registry);
    }

    function it_is_a_product_repository()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface');
    }

    function it_returns_eligible_products_for_variant_group($em, $class, Statement $statement, Connection $connection)
    {
        $em->getClassMetadata(Argument::any())->willReturn($class);
        $em->getConnection()->willReturn($connection);

        $variantGroupId = 10;
        $connection->prepare(Argument::any())->willReturn($statement);
        $statement->bindValue('groupId', $variantGroupId)->shouldBeCalled();
        $statement->execute()->willReturn(null);
        $statement->fetchAll()->willReturn([
            ['product_id' => 1],
            ['product_id' => 2],
        ]);

        $this->getEligibleProductIdsForVariantGroup($variantGroupId)->shouldReturn([1, 2]);
    }
}
