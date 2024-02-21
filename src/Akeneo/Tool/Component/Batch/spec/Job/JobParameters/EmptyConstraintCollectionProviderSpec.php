<?php

namespace spec\Akeneo\Tool\Component\Batch\Job\JobParameters;

use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraints\Collection;

class EmptyConstraintCollectionProviderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['job_name']);
    }

    function it_is_a_contraint_collection_provider()
    {
        $this->shouldImplement(ConstraintCollectionProviderInterface::class);
    }

    function it_provides_default_constraint_collection()
    {
        $this->getConstraintCollection()->shouldReturnAnInstanceOf(Collection::class);
    }
}
