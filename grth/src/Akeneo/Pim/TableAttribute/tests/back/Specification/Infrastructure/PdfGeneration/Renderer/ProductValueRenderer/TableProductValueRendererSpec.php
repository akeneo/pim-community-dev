<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\TableConfigurationFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\LoaderInterface;

class TableProductValueRendererSpec extends ObjectBehavior
{
    function let(
        TableConfigurationFactory $tableConfigurationFactory,
        TranslatorInterface $translator
    ) {
        $this->beConstructedWith($tableConfigurationFactory, $translator);
    }

    function it_renders_nothing_without_value(
        AttributeInterface $attribute,
        LoaderInterface $loader
    ) {
        $environment = new Environment($loader->getWrappedObject());

        $this->render($environment, $attribute, null, 'en_US')->shouldReturn('');
    }

    function it_renders_a_table(
        TableConfigurationFactory $tableConfigurationFactory,
        TranslatorInterface $translator,
        AttributeInterface $attribute,
        TableValue $value,
        LoaderInterface $loader
    ) {
        $environment = new Environment($loader->getWrappedObject());
        $aqrId = ColumnIdGenerator::generateAsString('aqr');
        $rawTableConfiguration = [
            ['id' => ColumnIdGenerator::ingredient(), 'data_type' => 'select', 'code' => 'ingredient', 'is_required_for_completeness' => true],
            ['id' => ColumnIdGenerator::quantity(), 'data_type' => 'number', 'code' => 'quantity'],
            ['id' => ColumnIdGenerator::isAllergenic(), 'data_type' => 'boolean', 'code' => 'is_allergenic'],
            ['id' => ColumnIdGenerator::description(), 'data_type' => 'text', 'code' => 'description'],
            ['id' => $aqrId, 'data_type' => 'select', 'code' => 'aqr'],
            ['id' => ColumnIdGenerator::duration(), 'data_type' => 'measurement', 'code' => 'duration', 'measurement_family_code' => 'duration', 'measurement_default_unit_code' => 's'],
        ];

        $attribute
            ->getRawTableConfiguration()
            ->shouldBeCalled()
            ->willReturn($rawTableConfiguration);

        $tableConfigurationFactory->createFromNormalized($rawTableConfiguration)->shouldBeCalled()->willReturn(
            TableConfiguration::fromColumnDefinitions([
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'data_type' => 'select', 'code' => 'ingredient', 'is_required_for_completeness' => true]),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'data_type' => 'number', 'code' => 'quantity']),
                BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'data_type' => 'boolean', 'code' => 'is_allergenic']),
                TextColumn::fromNormalized(['id' => ColumnIdGenerator::description(), 'data_type' => 'text', 'code' => 'description']),
                SelectColumn::fromNormalized(['id' => $aqrId, 'data_type' => 'select', 'code' => 'aqr']),
                MeasurementColumn::fromNormalized(['id' => ColumnIdGenerator::duration(), 'data_type' => 'measurement', 'code' => 'duration', 'measurement_family_code' => 'duration', 'measurement_default_unit_code' => 's']),            ])
        );

        $value
            ->getData()
            ->shouldBeCalled()
            ->willReturn(Table::fromNormalized([
                [
                    ColumnIdGenerator::ingredient() => 'sugar',
                    ColumnIdGenerator::quantity() => 42,
                    ColumnIdGenerator::isAllergenic() => true,
                    ColumnIdGenerator::description() => 'a <description>',
                    $aqrId => 'A',
                    ColumnIdGenerator::duration() => ['amount' => 12.5, 'unit' => 'm'],
                ],
                [
                    ColumnIdGenerator::ingredient() => 'salt',
                    ColumnIdGenerator::isAllergenic() => false,
                    ColumnIdGenerator::duration() => ['amount' => '5', 'unit' => 's']
                ],
                [ColumnIdGenerator::ingredient() => 'eggs'],
            ]));

        $translator->trans('Yes')->shouldBeCalled()->willReturn('Vrai');
        $translator->trans('No')->shouldBeCalled()->willReturn('Faux');

        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn('<table>
<thead><tr><th>ingredient</th><th>quantity</th><th>is_allergenic</th><th>description</th><th>aqr</th><th>duration</th></tr></thead>
<tbody><tr><td>sugar</td><td>42</td><td>Vrai</td><td>a &lt;description&gt;</td><td>A</td><td>12.5 m</td></tr>
<tr><td>salt</td><td></td><td>Faux</td><td></td><td></td><td>5 s</td></tr>
<tr><td>eggs</td><td></td><td></td><td></td><td></td><td></td></tr>
</tbody>
</table>');
    }
}
