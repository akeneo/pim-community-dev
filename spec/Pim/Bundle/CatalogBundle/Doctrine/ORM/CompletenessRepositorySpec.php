<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @require Doctrine\ODM\MongoDB\DocumentManager
 */
class CompletenessRepositorySpec extends ObjectBehavior
{
    function let(
        EntityManager $manager,
        Connection $connection,
        Statement $statement,
        ClassMetadata $classMetadata
    ) {
        $connection->prepare(Argument::any())->willReturn($statement);
        $manager->getClassMetadata(Argument::any())->willReturn($classMetadata);

        $manager->getConnection()->willReturn($connection);
        $this->beConstructedWith($manager, 'pim_product_class');
    }

    function it_is_a_completeness_repository()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Repository\CompletenessRepositoryInterface');
    }

    function it_counts_products_per_channels(Statement $statement)
    {
        $statement->execute()->willReturn(null);

        $statement->fetchAll()->willReturn(array(
            array('label' => 'ECommerce', 'total' => 3),
            array('label' => 'Mobile', 'total' => 2)
        ));

        $this->getProductsCountPerChannels()->shouldReturn(array(
            array('label' => 'ECommerce', 'total' => 3),
            array('label' => 'Mobile', 'total' => 2)
        ));
    }

    function it_counts_complete_products_per_channels(Statement $statement)
    {
        $statement->execute()->willReturn(null);

        $statement->fetchAll()->willReturn(array(
            array('locale' => 'en_US', 'label' => 'ECommerce', 'total' => 0),
            array('locale' => 'fr_FR', 'label' => 'ECommerce', 'total' => 1),
            array('locale' => 'en_US', 'label' => 'Mobile', 'total' => 2),
        ));

        $this->getCompleteProductsCountPerChannels()->shouldReturn(array(
            array('locale' => 'en_US', 'label' => 'ECommerce', 'total' => 0),
            array('locale' => 'fr_FR', 'label' => 'ECommerce', 'total' => 1),
            array('locale' => 'en_US', 'label' => 'Mobile', 'total' => 2),
        ));
    }
}
