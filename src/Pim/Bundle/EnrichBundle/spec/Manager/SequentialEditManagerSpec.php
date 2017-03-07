<?php

namespace spec\Pim\Bundle\EnrichBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Entity\Repository\SequentialEditRepository;
use Pim\Bundle\EnrichBundle\Entity\SequentialEdit;
use Pim\Bundle\EnrichBundle\Factory\SequentialEditFactory;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;

class SequentialEditManagerSpec extends ObjectBehavior
{
    function let(
        SequentialEditRepository $repository,
        SequentialEditFactory $factory,
        ProductRepositoryInterface $productRepository,
        RemoverInterface $remover
    ) {
        $this->beConstructedWith($repository, $factory, $productRepository, $remover);
    }

    function it_creates_an_entity($factory, UserInterface $user, SequentialEdit $sequentialEdit)
    {
        $factory->create([1, 3], $user)->willReturn($sequentialEdit);

        $this->createEntity([1, 3], $user)->shouldReturn($sequentialEdit);
    }

    function it_removes_a_sequential_edit_from_a_user($remover, $repository, UserInterface $user, SequentialEdit $sequentialEdit)
    {
        $repository->findOneBy(['user' => $user])->willReturn($sequentialEdit);

        $remover->remove($sequentialEdit)->shouldBeCalled();

        $this->removeByUser($user)->shouldReturn(null);
    }

    function it_does_not_remove_anything_if_user_have_no_sequential_edit($remover, $repository, UserInterface $user)
    {
        $repository->findOneBy(['user' => $user])->willReturn(null);

        $remover->remove(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->removeByUser($user)->shouldReturn(null);
    }

    function it_finds_a_sequential_edit_by_user($repository, UserInterface $user, SequentialEdit $sequentialEdit)
    {
        $repository->findOneBy(['user' => $user])->willReturn($sequentialEdit);

        $this->findByUser($user)->shouldReturn($sequentialEdit);
    }

    function it_find_previous_and_next_products(
        $productRepository,
        SequentialEdit $sequentialEdit,
        ProductInterface $product,
        ProductInterface $previous,
        ProductInterface $next
    ) {
        $sequentialEdit->getObjectSet()->willReturn([1, 6, 5, 2]);
        $sequentialEdit->countObjectSet()->willReturn(4);
        $product->getId()->willReturn(5);

        $productRepository->find(6)->willReturn($previous);
        $productRepository->find(2)->willReturn($next);

        $sequentialEdit->setCurrent($product)->shouldBeCalled();
        $sequentialEdit->setPrevious($previous)->shouldBeCalled();
        $sequentialEdit->setNext($next)->shouldBeCalled();

        $this->findWrap($sequentialEdit, $product);
    }
}
