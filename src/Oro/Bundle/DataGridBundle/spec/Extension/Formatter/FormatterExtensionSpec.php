<?php

declare(strict_types=1);

namespace spec\Oro\Bundle\DataGridBundle\Extension\Formatter;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsIterableObject;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\FormatterExtension;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Translation\TranslatorInterface;

class FormatterExtensionSpec extends ObjectBehavior
{
    function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator);
    }

    function it_is_a_formatter_extension()
    {
        $this->shouldBeAnInstanceOf(FormatterExtension::class);
    }

    function it_allows_a_column_configuration_with_an_integer(
        DatagridConfiguration $config,
        ResultsIterableObject $result,
        PropertyInterface $property1,
        PropertyInterface $property2,
        PropertyInterface $initializedProperty1,
        PropertyInterface $initializedProperty2
    ) {
        $this->registerProperty('property1', $property1);
        $this->registerProperty('property2', $property2);

        $record0 = new ResultRecord(['record0']);
        $record1 = new ResultRecord(['record1']);

        $rows = [
            '0' => $record0,
            '1' => $record1,
        ];

        $result->offsetGetOr('data', [])->willReturn($rows);
        $config->offsetGetOr(Configuration::COLUMNS_KEY, [])->willReturn([
            1234 => ['type' => 'property1']
        ]);
        $config->offsetGetOr(Configuration::PROPERTIES_KEY, [])->willReturn([
            'identifier' => ['type' => 'property2']
        ]);

        $config1234 = PropertyConfiguration::createNamed(1234, ['type' => 'property1']);
        $configIdentifier = PropertyConfiguration::createNamed('identifier', ['type' => 'property2']);
        $property1->init($config1234)->willReturn($initializedProperty1);
        $property2->init($configIdentifier)->willReturn($initializedProperty2);
        $initializedProperty1->getValue($record0)->willReturn('property1 record0');
        $initializedProperty1->getValue($record1)->willReturn('property1 record1');
        $initializedProperty2->getValue($record0)->willReturn('property2 record0');
        $initializedProperty2->getValue($record1)->willReturn('property2 record1');

        $result->offsetSet('data', [
            '0' => [
                1234 => 'property1 record0',
                'identifier' => 'property2 record0',
            ], '1' => [
                1234 => 'property1 record1',
                'identifier' => 'property2 record1',
            ]
        ])->shouldBeCalled();

        $this->visitResult($config, $result);
    }
}
