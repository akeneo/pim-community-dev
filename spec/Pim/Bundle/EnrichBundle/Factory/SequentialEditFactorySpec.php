<?php

namespace spec\Pim\Bundle\EnrichBundle\Factory;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\User\UserInterface;

class SequentialEditFactorySpec extends ObjectBehavior
{
    const SEQUENTIAL_EDIT_CLASS = 'Pim\Bundle\EnrichBundle\Entity\SequentialEdit';

    function let()
    {
        $this->beConstructedWith(self::SEQUENTIAL_EDIT_CLASS);
    }

    function it_creates_a_sequential_edit(UserInterface $user)
    {
        $this->create([1, 3], $user)->shouldReturnAnInstanceOf(self::SEQUENTIAL_EDIT_CLASS);
    }
}
