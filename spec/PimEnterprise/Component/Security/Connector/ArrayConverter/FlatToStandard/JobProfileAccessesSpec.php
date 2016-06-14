<?php

namespace spec\PimEnterprise\Component\Security\Connector\ArrayConverter\FlatToStandard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

class JobProfileAccessesSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldChecker)
    {
        $this->beConstructedWith($fieldChecker);
    }

    function it_converts_from_flat_to_standard_format()
    {
        $item = [
            'job_profile'         => 'product_import',
            'execute_job_profile' => 'IT support,Manager',
            'edit_job_profile'    => 'IT support',
        ];

        $expected = [
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

        $this->convert($item)->shouldReturn($expected);
    }
}
