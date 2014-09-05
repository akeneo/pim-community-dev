<?php

namespace spec\Pim\Bundle\EnrichBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\EnrichBundle\Entity\Repository\SequentialEditRepository;
use Pim\Bundle\EnrichBundle\Entity\SequentialEdit;
use Pim\Bundle\EnrichBundle\Factory\SequentialEditFactory;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;

class SequentialEditManagerSpec extends ObjectBehavior
{
    function let(
        ObjectManager $om,
        SequentialEditRepository $repository,
        SequentialEditFactory $factory,
        ProductManager $productManager
    ) {
        $this->beConstructedWith($om, $repository, $factory, $productManager);
    }

    function it_saves_a_sequential_edit(SequentialEdit $sequentialEdit, $om)
    {
        $om->persist($sequentialEdit)->shouldBeCalled();
        $om->flush($sequentialEdit)->shouldBeCalled();

        $this->save($sequentialEdit)->shouldReturn(null);
    }

    function it_creates_an_entity($factory, UserInterface $user, SequentialEdit $sequentialEdit)
    {
        $factory->create([1, 3], $user)->willReturn($sequentialEdit);

        $this->createEntity([1, 3], $user)->shouldReturn($sequentialEdit);
    }

    function it_removes_a_sequential_edit($om, SequentialEdit $sequentialEdit)
    {
        $om->remove($sequentialEdit)->shouldBeCalled();
        $om->flush($sequentialEdit)->shouldBeCalled();

        $this->remove($sequentialEdit)->shouldReturn(null);
    }

    function it_removes_a_sequential_edit_from_a_user($om, $repository, UserInterface $user, SequentialEdit $sequentialEdit)
    {
        $repository->findOneBy(['user' => $user])->willReturn($sequentialEdit);
        $om->remove($sequentialEdit)->shouldBeCalled();
        $om->flush($sequentialEdit)->shouldBeCalled();

        $this->removeFromUser($user)->shouldReturn(null);
    }

    function it_does_not_remove_anything_if_user_have_no_sequential_edit($om, $repository, UserInterface $user)
    {
        $repository->findOneBy(['user' => $user])->willReturn(null);
        $om->remove(Argument::any())->shouldNotBeCalled();
        $om->flush(Argument::any())->shouldNotBeCalled();

        $this->removeFromUser($user)->shouldReturn(null);
    }

    function it_finds_a_sequential_edit_by_user($repository, UserInterface $user, SequentialEdit $sequentialEdit)
    {
        $repository->findOneBy(['user' => $user])->willReturn($sequentialEdit);

        $this->findByUser($user)->shouldReturn($sequentialEdit);
    }
}
