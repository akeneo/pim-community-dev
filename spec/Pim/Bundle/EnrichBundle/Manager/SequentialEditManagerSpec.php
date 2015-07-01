<?php

namespace spec\Pim\Bundle\EnrichBundle\Manager;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\Entity\Repository\SequentialEditRepository;
use Pim\Bundle\EnrichBundle\Entity\SequentialEdit;
use Pim\Bundle\EnrichBundle\Factory\SequentialEditFactory;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;

class SequentialEditManagerSpec extends ObjectBehavior
{
    function let(
        SequentialEditRepository $repository,
        SequentialEditFactory $factory,
        ProductManager $productManager,
        SaverInterface $saver,
        RemoverInterface $remover
    ) {
        $this->beConstructedWith($repository, $factory, $productManager, $saver, $remover);
    }

    function it_saves_a_sequential_edit($saver, SequentialEdit $sequentialEdit)
    {
        $saver->save($sequentialEdit, [])->shouldBeCalled();

        $this->save($sequentialEdit)->shouldReturn(null);
    }

    function it_creates_an_entity($factory, UserInterface $user, SequentialEdit $sequentialEdit)
    {
        $factory->create([1, 3], $user)->willReturn($sequentialEdit);

        $this->createEntity([1, 3], $user)->shouldReturn($sequentialEdit);
    }

    function it_removes_a_sequential_edit($remover, SequentialEdit $sequentialEdit)
    {
        $remover->remove($sequentialEdit, [])->shouldBeCalled();

        $this->remove($sequentialEdit)->shouldReturn(null);
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
        $productManager,
        SequentialEdit $sequentialEdit,
        ProductInterface $product,
        ProductInterface $previous,
        ProductInterface $next
    ) {
        $sequentialEdit->getObjectSet()->willReturn([1, 6, 5, 2]);
        $sequentialEdit->countObjectSet()->willReturn(4);
        $product->getId()->willReturn(5);

        $productManager->find(6)->willReturn($previous);
        $productManager->find(2)->willReturn($next);

        $sequentialEdit->setCurrent($product)->shouldBeCalled();
        $sequentialEdit->setPrevious($previous)->shouldBeCalled();
        $sequentialEdit->setNext($next)->shouldBeCalled();

        $this->findWrap($sequentialEdit, $product);
    }

    function it_is_a_saver()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Saver\SaverInterface');
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Akeneo\Component\StorageUtils\Remover\RemoverInterface');
    }
}
