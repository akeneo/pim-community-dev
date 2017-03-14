<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Completeness\CompletenessCalculatorInterface;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\CompletenessInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductSaverSpec extends ObjectBehavior
{
    function let(
        EntityManagerInterface $entityManager,
        CompletenessCalculatorInterface $completenessCalculator,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith($entityManager, $completenessCalculator, $eventDispatcher);
    }

    function it_is_a_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldHaveType('Akeneo\Component\StorageUtils\Saver\BulkSaverInterface');
    }

    function it_saves_a_product_after_droping_its_previous_completenesses(
        $entityManager,
        $completenessCalculator,
        $eventDispatcher,
        ProductInterface $product,
        Connection $connection,
        Statement $statement,
        ArrayCollection $completenessesCollection,
        \ArrayIterator $completenessesIterator,
        CompletenessInterface $oldCompleteness,
        CompletenessInterface $newCompleteness
    ) {
        $completenessesCollection->getIterator()->willReturn($completenessesIterator);
        $completenessesIterator->rewind()->shouldBeCalled();
        $completenessesIterator->valid()->willReturn(true, false, false);
        $completenessesIterator->current()->willReturn($oldCompleteness);
        $completenessesIterator->next()->shouldBeCalled();

        $entityManager->getConnection()->willReturn($connection);
        $oldCompleteness->getId()->willReturn(42);
        $connection->executeQuery(
            'DELETE c FROM pim_catalog_completeness c WHERE c.id IN (?)',
            [[42]],
            [Connection::PARAM_INT_ARRAY]
        )->willReturn($statement);
        $statement->execute()->shouldBeCalled();

        $product->getCompletenesses()->willReturn($completenessesCollection);
        $completenessesCollection->isEmpty()->willReturn(false);
        $completenessesCollection->clear()->shouldBeCalled();

        $completenessCalculator->calculate($product)->willReturn([$newCompleteness]);
        $completenessesCollection->add($newCompleteness)->shouldBeCalled();

        $entityManager->persist($product)->shouldBeCalled();
        $entityManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalled();

        $this->save($product);
    }

    function it_saves_multiple_products_after_droping_their_previous_completenesses(
        $entityManager,
        $completenessCalculator,
        $eventDispatcher,
        ProductInterface $product1,
        ProductInterface $product2,
        Connection $connection,
        Statement $statement,
        ArrayCollection $completenessesCollection1,
        ArrayCollection $completenessesCollection2,
        \ArrayIterator $completenessesIterator1,
        \ArrayIterator $completenessesIterator2,
        CompletenessInterface $oldCompleteness1,
        CompletenessInterface $oldCompleteness2,
        CompletenessInterface $newCompleteness1,
        CompletenessInterface $newCompleteness2
    ) {
        $completenessesCollection1->getIterator()->willReturn($completenessesIterator1);
        $completenessesIterator1->rewind()->shouldBeCalled();
        $completenessesIterator1->valid()->willReturn(true, false);
        $completenessesIterator1->current()->willReturn($oldCompleteness1);
        $completenessesIterator1->next()->shouldBeCalled();

        $completenessesCollection2->getIterator()->willReturn($completenessesIterator2);
        $completenessesIterator2->rewind()->shouldBeCalled();
        $completenessesIterator2->valid()->willReturn(true, false);
        $completenessesIterator2->current()->willReturn($oldCompleteness2);
        $completenessesIterator2->next()->shouldBeCalled();

        $oldCompleteness1->getId()->willReturn(42);
        $oldCompleteness2->getId()->willReturn(43);

        $entityManager->getConnection()->willReturn($connection);
        $connection->executeQuery(
            'DELETE c FROM pim_catalog_completeness c WHERE c.id IN (?)',
            [[42, 43]],
            [Connection::PARAM_INT_ARRAY]
        )->willReturn($statement);
        $statement->execute()->shouldBeCalled();

        $product1->getCompletenesses()->willReturn($completenessesCollection1);
        $completenessesCollection1->isEmpty()->willReturn(true);
        $completenessesCollection1->clear()->shouldNotBeCalled();

        $product2->getCompletenesses()->willReturn($completenessesCollection2);
        $completenessesCollection2->isEmpty()->willReturn(false);
        $completenessesCollection2->clear()->shouldBeCalled();

        $completenessCalculator->calculate($product1)->willReturn([$newCompleteness1]);
        $completenessCalculator->calculate($product2)->willReturn([$newCompleteness2]);
        $completenessesCollection1->add($newCompleteness1)->shouldBeCalled();
        $completenessesCollection2->add($newCompleteness2)->shouldBeCalled();

        $entityManager->persist($product1)->shouldBeCalled();
        $entityManager->persist($product2)->shouldBeCalled();

        $entityManager->flush()->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, Argument::cetera())->shouldBeCalled();
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, Argument::cetera())->shouldBeCalled();

        $eventDispatcher->dispatch(StorageEvents::PRE_SAVE, Argument::cetera())->shouldBeCalledTimes(2);
        $eventDispatcher->dispatch(StorageEvents::POST_SAVE, Argument::cetera())->shouldBeCalledTimes(2);

        $this->saveAll([$product1, $product2]);
    }

    function it_throws_an_exception_when_try_to_save_something_else_than_a_product(
        $entityManager
    ) {
        $otherObject = new \stdClass();
        $entityManager->persist(Argument::any())->shouldNotBeCalled();

        $this
            ->shouldThrow(new \InvalidArgumentException('Expects a Pim\Component\Catalog\Model\ProductInterface, "stdClass" provided'))
            ->duringSave($otherObject);
    }
}
