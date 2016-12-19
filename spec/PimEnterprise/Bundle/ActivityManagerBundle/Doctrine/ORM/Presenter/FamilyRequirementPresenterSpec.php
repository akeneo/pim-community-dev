<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Presenter;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Presenter\FamilyRequirementPresenter;
use PimEnterprise\Component\ActivityManager\Presenter\PresenterInterface;

class FamilyRequirementPresenterSpec extends ObjectBehavior
{
    function let(PresenterInterface $presenter)
    {
        $this->beConstructedWith($presenter);
    }

    function it_is_a_presenter()
    {
        $this->shouldImplement(PresenterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyRequirementPresenter::class);
    }

    function it_presents($presenter)
    {
        $mandatoryAttributes = [
            [
                'attribute_code'       => 'sku',
                'attribute_group_code' => 'marketing',
            ],
            [
                'attribute_code'       => 'name',
                'attribute_group_code' => 'marketing',
            ],
            [
                'attribute_code'       => 'size',
                'attribute_group_code' => 'design',
            ],
            [
                'attribute_code'       => 'style',
                'attribute_group_code' => 'design',
            ],
            [
                'attribute_code'       => 'weigh',
                'attribute_group_code' => 'technical',
            ],
            [
                'attribute_code'       => 'height',
                'attribute_group_code' => 'technical',
            ],
        ];

        $results = [
            'marketing' => [
                'sku',
                'name',
            ],
            'design' => [
                'size',
                'style',
            ],
            'technical' => [
                'weigh',
                'height',
            ],
        ];

        $presenter->present($mandatoryAttributes)->willReturn($results);

        $this->present($mandatoryAttributes)->shouldReturn($results);
    }
}
