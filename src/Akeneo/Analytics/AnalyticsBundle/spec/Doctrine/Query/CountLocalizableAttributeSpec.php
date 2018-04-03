<?php

namespace spec\Pim\Bundle\AnalyticsBundle\Doctrine\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\AnalyticsBundle\Doctrine\Query\CountLocalizableAttribute;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CountLocalizableAttributeSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CountLocalizableAttribute::class);
    }
}
