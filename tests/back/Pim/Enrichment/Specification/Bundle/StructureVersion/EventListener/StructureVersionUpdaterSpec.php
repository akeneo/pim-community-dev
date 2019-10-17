<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\StructureVersion\EventListener;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class StructureVersionUpdaterSpec extends ObjectBehavior
{
    function let(RegistryInterface $registry, Connection $connection)
    {
        $registry->getConnection()->willReturn($connection);
        $this->beConstructedWith($registry);
    }

    function it_inserts_unitary_if_the_option_is_set_to_true(Connection $connection)
    {
        $event = new GenericEvent(new \stdClass());
        $event->setArgument('unitary', true);
        $connection->executeUpdate(Argument::cetera())->shouldBeCalled();

        $this->onPostSave($event);
    }

    function it_inserts_on_bulk_operation(Connection $connection)
    {
        $event = new GenericEvent([new \stdClass()]);
        $event->setArgument('unitary', true);
        $connection->executeUpdate(Argument::cetera())->shouldBeCalled();

        $this->onPostSaveAll($event);
    }

    function it_does_not_insert_unitary_if_the_option_is_set_to_false(Connection $connection)
    {
        $event = new GenericEvent(new \stdClass());
        $event->setArgument('unitary', false);
        $connection->executeUpdate(Argument::cetera())->shouldNotBeCalled();

        $this->onPostSave($event);
    }

    function it_does_not_insert_unitary_if_the_option_is_not_set(Connection $connection)
    {
        $event = new GenericEvent(new \stdClass());
        $connection->executeUpdate(Argument::cetera())->shouldNotBeCalled();

        $this->onPostSave($event);
    }

    function it_does_not_insert_into_the_structure_version_table_any_information_about_product_to_avoid_costly_requests(Connection $connection)
    {
        $event = new GenericEvent(new Product());
        $connection->executeUpdate(Argument::cetera())->shouldNotBeCalled();

        $this->onPostSave($event);
    }

    function it_does_not_insert_into_the_structure_version_table_any_information_about_product_in_bulk_operation(Connection $connection)
    {
        $event = new GenericEvent([new Product()]);
        $connection->executeUpdate(Argument::cetera())->shouldNotBeCalled();

        $this->onPostSaveAll($event);
    }
}
