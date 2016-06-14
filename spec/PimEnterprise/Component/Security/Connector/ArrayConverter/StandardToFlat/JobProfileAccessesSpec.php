<?php

namespace spec\PimEnterprise\Component\Security\Connector\ArrayConverter\StandardToFlat;

use PhpSpec\ObjectBehavior;

class JobProfileAccessesSpec extends ObjectBehavior
{
    function it_converts_from_standard_to_flat_format()
    {
        $item = [
            [
                'job_profile'         => 'product_import',
                'user_group'          => 'IT support',
                'execute_job_profile' => true,
                'edit_job_profile'    => true,
            ],
            [
                'job_profile'         => 'product_import',
                'user_group'          => 'Manager',
                'execute_job_profile' => true,
                'edit_job_profile'    => false,
            ]
        ];

        $expected = [
            'job_profile'         => 'product_import',
            'execute_job_profile' => 'IT support,Manager',
            'edit_job_profile'    => 'IT support',
        ];

        $this->convert($item)->shouldReturn($expected);
    }
}
