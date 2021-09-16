<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\ColumnFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

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
        TableValue $value
    ) {
        $environment = new Environment();
        $attribute
            ->getRawTableConfiguration()
            ->shouldBeCalled()
            ->willReturn([
                ['data_type' => 'select', 'code' => 'ingredient'],
                ['data_type' => 'number', 'code' => 'quantity'],
                ['data_type' => 'boolean', 'code' => 'is_allergenic'],
                ['data_type' => 'text', 'code' => 'description'],
                ['data_type' => 'select', 'code' => 'aqr']
            ]);
        $columnFactory
            ->createFromNormalized(['data_type' => 'select', 'code' => 'ingredient'])
            ->shouldBeCalled()
            ->willReturn(SelectColumn::fromNormalized(['data_type' => 'select', 'code' => 'ingredient']));
        $columnFactory
            ->createFromNormalized(['data_type' => 'number', 'code' => 'quantity'])
            ->shouldBeCalled()
            ->willReturn(NumberColumn::fromNormalized(['data_type' => 'number', 'code' => 'quantity']));
        $columnFactory
            ->createFromNormalized(['data_type' => 'boolean', 'code' => 'is_allergenic'])
            ->shouldBeCalled()
            ->willReturn(BooleanColumn::fromNormalized(['data_type' => 'boolean', 'code' => 'is_allergenic']));
        $columnFactory
            ->createFromNormalized(['data_type' => 'text', 'code' => 'description'])
            ->shouldBeCalled()
            ->willReturn(TextColumn::fromNormalized(['data_type' => 'text', 'code' => 'description']));
        $columnFactory
            ->createFromNormalized(['data_type' => 'select', 'code' => 'aqr'])
            ->shouldBeCalled()
            ->willReturn(SelectColumn::fromNormalized(['data_type' => 'select', 'code' => 'aqr']));

        $value
            ->getData()
            ->shouldBeCalled()
            ->willReturn(Table::fromNormalized([
                ['ingredient' => 'sugar', 'quantity' => 42, 'is_allergenic' => true, 'description' => 'a <description>', 'aqr' => 'A'],
                ['ingredient' => 'salt', 'is_allergenic' => false],
                ['ingredient' => 'eggs'],
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
