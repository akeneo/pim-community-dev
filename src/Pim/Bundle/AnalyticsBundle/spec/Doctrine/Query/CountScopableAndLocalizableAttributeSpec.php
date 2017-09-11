<?php

namespace spec\Pim\Bundle\AnalyticsBundle\Doctrine\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\AnalyticsBundle\Doctrine\Query\CountScopableAndLocalizableAttribute;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CountScopableAndLocalizableAttributeSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CountScopableAndLocalizableAttribute::class);
    }
}
