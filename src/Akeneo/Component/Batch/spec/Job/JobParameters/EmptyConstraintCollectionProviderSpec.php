<?php

namespace spec\Akeneo\Component\Batch\Job\JobParameters;

use PhpSpec\ObjectBehavior;

class EmptyConstraintCollectionProviderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['job_name']);
    }

    function it_is_a_contraint_collection_provider()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface');
    }

    function it_provides_default_constraint_collection()
    {
        $collectionClass = 'Symfony\Component\Validator\Constraints\Collection';
        $this->getConstraintCollection()->shouldReturnAnInstanceOf($collectionClass);
    }
}
