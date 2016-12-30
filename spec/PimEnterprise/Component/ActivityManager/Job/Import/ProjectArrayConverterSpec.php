<?php

namespace spec\PimEnterprise\Component\ActivityManager\Job\Import;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use PimEnterprise\Component\ActivityManager\Job\Import\ProjectArrayConverter;
use PhpSpec\ObjectBehavior;

class ProjectArrayConverterSpec extends ObjectBehavior
{
    function let(FieldsRequirementChecker $fieldsRequirementChecker)
    {
        $this->beConstructedWith($fieldsRequirementChecker);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProjectArrayConverter::class);
    }

    function it_is_an_array_converter()
    {
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_converts_flat_project_to_standard_format($fieldsRequirementChecker)
    {
        $dataToConvert = [
            'label' => 'My label',
            'description' => 'description',
            'due_date' => '2018-07-21',
            'product_filters' => 'a:4:{s:5:"field";s:6:"family";s:8:"operator";s:2:"IN";s:5:"value";a:1:{i:0;s:4:"mugs";}s:7:"context";a:2:{s:6:"locale";s:5:"en_US";s:5:"scope";s:9:"ecommerce";}}',
            'owner' => 'admin',
            'locale' => 'fr_FR',
            'channel' => 'print',
            'datagrid_view-columns' => 'my json columns',
            'datagrid_view-filters' => '/filters',
        ];

        $mandatoriesField = ['owner', 'label', 'locale', 'channel', 'datagrid_view-columns', 'datagrid_view-filters'];

        $fieldsRequirementChecker->checkFieldsPresence($dataToConvert, $mandatoriesField)
            ->shouldBeCalled();
        $fieldsRequirementChecker->checkFieldsFilling($dataToConvert, $mandatoriesField)
            ->shouldBeCalled();

        $this->convert($dataToConvert)->shouldReturn([
            'label' => 'My label',
            'description' => 'description',
            'due_date' => '2018-07-21',
            'product_filters' => [
                'field' => 'family',
                'operator' => 'IN',
                'value' => ['mugs'],
                'context' => ['locale' => 'en_US','scope' => 'ecommerce']
            ],
            'owner' => 'admin',
            'locale' => 'fr_FR',
            'channel' => 'print',
            'datagrid_view' => [
                'columns' => 'my json columns',
                'filters' => '/filters',
            ]
        ]);
    }
}
