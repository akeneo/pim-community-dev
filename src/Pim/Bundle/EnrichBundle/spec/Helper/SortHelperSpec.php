<?php

namespace spec\Pim\Bundle\EnrichBundle\Helper;

use PhpSpec\ObjectBehavior;

class SortHelperSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Helper\SortHelper');
    }

    function it_sorts_arrays_of_strings()
    {
        $sortables = [
            'unsorted' => [1 => 'foo', 2 => 'bar', 3 => 'baz'],
            'sorted'   => [2 => 'bar', 3 => 'baz', 1 => 'foo']
        ];
        $this::sort($sortables['unsorted'])
            ->shouldReturn($sortables['sorted']);
    }

    function it_sorts_arrays_of_numbers()
    {
        $sortables = [
            'unsorted' => ['a' => 52, 'b' => 2, 'e' => 03],
            'sorted'   => ['b' => 2, 'e' => 03, 'a' => 52]
        ];
        $this::sort($sortables['unsorted'])
            ->shouldReturn($sortables['sorted']);
    }

    function it_sorts_objects_by_key()
    {
        $foo = new Sortable('1');
        $bar = new Sortable('2');
        $baz = new Sortable('3');

        $this::sortByProperty([$bar, $foo, $baz], 'key')
            ->shouldReturn([1 => $foo, 0 => $bar, 2 => $baz]);
    }
}

class Sortable
{
    public $key;

    public function __construct($key)
    {
        $this->key = $key;
    }
}
