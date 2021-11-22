<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\ColumnFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
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
        ColumnFactory $columnFactory,
        TranslatorInterface $translator
    ) {
        $this->beConstructedWith($columnFactory, $translator);
    }

    function it_renders_a_table(
        ColumnFactory $columnFactory,
        TranslatorInterface $translator,
        AttributeInterface $attribute,
        TableValue $value,
        LoaderInterface $loader
    ) {
        $environment = new Environment($loader->getWrappedObject());
        $aqrId = ColumnIdGenerator::generateAsString('aqr');
        $attribute
            ->getRawTableConfiguration()
            ->shouldBeCalled()
            ->willReturn([
                ['id' => ColumnIdGenerator::ingredient(), 'data_type' => 'select', 'code' => 'ingredient'],
                ['id' => ColumnIdGenerator::quantity(), 'data_type' => 'number', 'code' => 'quantity'],
                ['id' => ColumnIdGenerator::isAllergenic(), 'data_type' => 'boolean', 'code' => 'is_allergenic'],
                ['id' => ColumnIdGenerator::description(), 'data_type' => 'text', 'code' => 'description'],
                ['id' => $aqrId, 'data_type' => 'select', 'code' => 'aqr']
            ]);
        $columnFactory
            ->createFromNormalized(['id' => ColumnIdGenerator::ingredient(), 'data_type' => 'select', 'code' => 'ingredient'])
            ->shouldBeCalled()
            ->willReturn(SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'data_type' => 'select', 'code' => 'ingredient']));
        $columnFactory
            ->createFromNormalized(['id' => ColumnIdGenerator::quantity(), 'data_type' => 'number', 'code' => 'quantity'])
            ->shouldBeCalled()
            ->willReturn(NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'data_type' => 'number', 'code' => 'quantity']));
        $columnFactory
            ->createFromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'data_type' => 'boolean', 'code' => 'is_allergenic'])
            ->shouldBeCalled()
            ->willReturn(BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'data_type' => 'boolean', 'code' => 'is_allergenic']));
        $columnFactory
            ->createFromNormalized(['id' => ColumnIdGenerator::description(), 'data_type' => 'text', 'code' => 'description'])
            ->shouldBeCalled()
            ->willReturn(TextColumn::fromNormalized(['id' => ColumnIdGenerator::description(), 'data_type' => 'text', 'code' => 'description']));
        $columnFactory
            ->createFromNormalized(['id' => $aqrId, 'data_type' => 'select', 'code' => 'aqr'])
            ->shouldBeCalled()
            ->willReturn(SelectColumn::fromNormalized(['id' => $aqrId, 'data_type' => 'select', 'code' => 'aqr']));

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
                ],
                [ColumnIdGenerator::ingredient() => 'salt', ColumnIdGenerator::isAllergenic() => false],
                [ColumnIdGenerator::ingredient() => 'eggs'],
            ]));

        $translator->trans('Yes')->shouldBeCalled()->willReturn('Vrai');
        $translator->trans('No')->shouldBeCalled()->willReturn('Faux');

        $this->render($environment, $attribute, $value, 'en_US')->shouldReturn('<table>
<thead><tr><th>ingredient</th><th>quantity</th><th>is_allergenic</th><th>description</th><th>aqr</th></tr></thead>
<tbody><tr><td>sugar</td><td>42</td><td>Vrai</td><td>a &lt;description&gt;</td><td>A</td></tr>
<tr><td>salt</td><td></td><td>Faux</td><td></td><td></td></tr>
<tr><td>eggs</td><td></td><td></td><td></td><td></td></tr>
</tbody>
</table>');
    }
}
