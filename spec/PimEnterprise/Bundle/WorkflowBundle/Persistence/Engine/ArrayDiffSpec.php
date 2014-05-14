<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Persistence\Engine;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ArrayDiffSpec extends ObjectBehavior
{
    function it_finds_no_difference_between_two_identical_unidimensional_arrays()
    {
        $this->diff([], [])->shouldReturn([]);
    }

    function it_finds_added_values_between_two_unidimensional_arrays()
    {
        $b = [
            'a' => 'z',
            'b' => 'y',
        ];

        $this->diff([], $b)->shouldReturn($b);
    }

    function it_finds_removed_values_between_two_unidimensional_arrays()
    {
        $a = [
            'a' => 'z',
            'b' => 'y',
        ];

        $this->diff($a, [])->shouldReturn([
            'a' => null,
            'b' => null,
        ]);
    }

    function it_finds_differences_between_two_different_unidimensional_arrays()
    {
        $a = [
            'a' => 'z',
            'b' => 'y',
        ];

        $b = [
            'b' => 'y',
            'a' => 'x',
        ];

        $this->diff($a, $b)->shouldReturn(['a' => 'x']);
    }

    function it_finds_no_difference_between_two_identical_multidimensional_arrays()
    {
        $a = $b = [
            'a' => [
                'aa' => 'zz'
            ],
            'b' => [
                'bb' => [
                    'bbb' => 'yyy'
                ]
            ]
        ];

        $this->diff($a, $b)->shouldReturn([]);
    }

    function it_finds_added_values_between_two_multidimensional_arrays()
    {
        $a = [
            'a' => [
                'aa' => 'zz'
            ],
            'b' => [
                'bb' => [
                    'bbb' => 'yyy',
                ]
            ]
        ];

        $b = [
            'a' => [
                'aa' => 'zz'
            ],
            'b' => [
                'bb' => [
                    'bbb' => 'yyy',
                    'ccc' => 'www',
                ]
            ],
            'c' => [
                'cc' => [
                    'ccc' => 'vvv'
                ]
            ]
        ];

        $this->diff($a, $b)->shouldReturn([
            'b' => [
                'bb' => [
                    'ccc' => 'www'
                ]
            ],
            'c' => [
                'cc' => [
                    'ccc' => 'vvv'
                ]
            ]
        ]);
    }

    function it_finds_removed_values_between_two_multidimensional_arrays()
    {
        $a = [
            'a' => [
                'aa' => 'zz'
            ],
            'b' => [
                'bb' => [
                    'bbb' => 'yyy',
                ]
            ]
        ];

        $b = [
            'a' => [],
        ];

        $this->diff($a, $b)->shouldReturn([
            'a' => [
                'aa' => null,
            ],
            'b' => null
        ]);
    }

    function it_finds_differences_between_two_different_multi_dimensional_arrays()
    {
        $a = [
            'a' => [
                'aa' => 'zz'
            ],
            'b' => [
                'bb' => [
                    'bbb' => 'yyy',
                    'ccc' => 'www',
                ]
            ]
        ];

        $b = [
            'a' => [
                'aa' => 'zz'
            ],
            'b' => [
                'bb' => [
                    'bbb' => 'xxx',
                    'ccc' => 'www',
                ]
            ]
        ];

        $this->diff($a, $b)->shouldReturn([
            'b' => [
                'bb' => [
                    'bbb' => 'xxx'
                ]
            ]
        ]);
    }
}
