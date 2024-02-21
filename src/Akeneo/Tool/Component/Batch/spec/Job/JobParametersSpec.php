<?php

namespace spec\Akeneo\Tool\Component\Batch\Job;

use Akeneo\Tool\Component\Batch\Job\UndefinedJobParameterException;
use PhpSpec\ObjectBehavior;

class JobParametersSpec extends ObjectBehavior
{
    function it_contains_a_parameter()
    {
        $this->beConstructedWith(['filePath' => '/tmp/myfile.csv']);
        $this->has('filePath')->shouldReturn(true);
    }

    function it_does_not_contain_a_parameter()
    {
        $this->beConstructedWith(['filePath' => '/tmp/myfile.csv']);
        $this->has('enclosure')->shouldReturn(false);
    }

    function it_is_countable()
    {
        $this->beConstructedWith([]);
        $this->shouldImplement('\Countable');
    }

    function it_is_iterable()
    {
        $this->beConstructedWith([]);
        $this->shouldImplement('\IteratorAggregate');
    }

    function it_counts_the_number_of_parameters()
    {
        $this->beConstructedWith(['filePath' => '/tmp/myfile.csv', 'enclosure' => '"']);
        $this->count()->shouldReturn(2);
    }

    function it_provides_a_parameter_value()
    {
        $this->beConstructedWith(['filePath' => '/tmp/myfile.csv', 'enclosure' => '"']);
        $this->get('filePath')->shouldReturn('/tmp/myfile.csv');
    }

    function it_provides_all_parameter_values_as_array()
    {
        $this->beConstructedWith(['filePath' => '/tmp/myfile.csv', 'enclosure' => '"']);
        $this->all()->shouldReturn(['filePath' => '/tmp/myfile.csv', 'enclosure' => '"']);
    }

    function it_throws_undefined_job_parameter_exception_when_accessing_undefined_parameter()
    {
        $this->beConstructedWith(['filePath' => '/tmp/myfile.csv', 'enclosure' => '"']);
        $this->shouldThrow(
            new UndefinedJobParameterException('Parameter "undefinedKey" is undefined')
        )->during(
            'get',
            ['undefinedKey']
        );
    }
}
