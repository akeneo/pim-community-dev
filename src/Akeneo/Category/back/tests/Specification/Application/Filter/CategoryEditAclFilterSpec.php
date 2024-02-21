<?php

namespace Specification\Akeneo\Category\Application\Filter;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;

class CategoryEditAclFilterSpec extends ObjectBehavior
{
    function let(SecurityFacade $securityFacade)
    {
        $this->beConstructedWith($securityFacade);
    }

    function it_filters_attributes_data_when_not_granted($securityFacade)
    {
        $securityFacade->isGranted('pim_enrich_product_category_edit_attributes')->willReturn(false);

        $this->filterCollection($this->getData())->shouldReturn([
            'code' => 'a_code',
            'labels' => [
                'en_US' => 'A code'
            ],
        ]);
    }

    function it_does_not_filters_attributes_data_when_granted($securityFacade)
    {
        $data = $this->getData();

        $securityFacade->isGranted('pim_enrich_product_category_edit_attributes')->willReturn(true);

        $this->filterCollection($data)->shouldReturn($data);
    }

    private function getData(): array
    {
        return [
            'code' => 'a_code',
            'labels' => [
                'en_US' => 'A code'
            ],
            'values' => [
                'text_value|uuid|en_US' => [
                    'data' => 'a text value',
                    'locale' => 'en_US',
                    'attribute_code' => 'text_value|uuid'
                ]
            ]
        ];
    }
}
