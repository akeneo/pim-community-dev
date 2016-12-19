<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Presenter;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Presenter\AttributePresenter;
use PimEnterprise\Component\ActivityManager\Presenter\PresenterInterface;

class AttributePresenterSpec extends ObjectBehavior
{
    function it_is_a_presenter()
    {
        $this->shouldImplement(PresenterInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributePresenter::class);
    }

    function it_presents()
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

        $this->present($mandatoryAttributes)->shouldReturn($results);
    }
}
