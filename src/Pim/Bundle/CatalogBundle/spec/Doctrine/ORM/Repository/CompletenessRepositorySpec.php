<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CompletenessRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $manager,
        Connection $connection,
        Statement $statement,
        ClassMetadata $classMetadata
    ) {
        $connection->executeQuery(Argument::any(), ['locale' => 'en_US'])->willReturn($statement);
        $manager->getClassMetadata(Argument::any())->willReturn($classMetadata);

        $manager->getConnection()->willReturn($connection);
        $this->beConstructedWith($manager, 'pim_product_class');
    }

    function it_is_a_completeness_repository()
    {
        $this->shouldImplement('Pim\Component\Catalog\Repository\CompletenessRepositoryInterface');
    }

    function it_counts_products_per_channels($statement)
    {
        $statement->fetchAll()->willReturn(
            [
                ['label' => 'ECommerce', 'total' => 3],
                ['label' => 'Mobile', 'total' => 2]
            ]
        );

        $this->getProductsCountPerChannels('en_US')->shouldReturn(
            [
                ['label' => 'ECommerce', 'total' => 3],
                ['label' => 'Mobile', 'total' => 2]
            ]
        );
    }

    function it_counts_complete_products_per_channels($statement)
    {
        $statement->fetchAll()->willReturn(
            [
                ['locale' => 'en_US', 'label' => 'ECommerce', 'total' => 0],
                ['locale' => 'fr_FR', 'label' => 'ECommerce', 'total' => 1],
                ['locale' => 'en_US', 'label' => 'Mobile', 'total' => 2],
            ]
        );

        $this->getCompleteProductsCountPerChannels('en_US')->shouldReturn(
            [
                ['locale' => 'en_US', 'label' => 'ECommerce', 'total' => 0],
                ['locale' => 'fr_FR', 'label' => 'ECommerce', 'total' => 1],
                ['locale' => 'en_US', 'label' => 'Mobile', 'total' => 2],
            ]
        );
    }
}
