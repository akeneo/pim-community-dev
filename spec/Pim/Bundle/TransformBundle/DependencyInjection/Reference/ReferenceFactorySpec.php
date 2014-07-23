<?php

namespace spec\Pim\Bundle\TransformBundle\DependencyInjection\Reference;

use PhpSpec\ObjectBehavior;

class ReferenceFactorySpec extends ObjectBehavior
{
    function it_creates_a_reference()
    {
        $this
            ->createReference('foo')
            ->shouldReturnAnInstanceOf('Symfony\Component\DependencyInjection\Reference');
    }
}
