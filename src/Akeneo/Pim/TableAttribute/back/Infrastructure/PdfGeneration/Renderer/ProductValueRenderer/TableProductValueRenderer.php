<?php

namespace Akeneo\Pim\TableAttribute\Infrastructure\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\ProductValueRenderer;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\ColumnFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Webmozart\Assert\Assert;

class TableProductValueRenderer implements ProductValueRenderer
{
    private ColumnFactory $columnFactory;
    private TranslatorInterface $translator;

    public function __construct(ColumnFactory $columnFactory, TranslatorInterface $translator)
    {
        $this->columnFactory = $columnFactory;
        $this->translator = $translator;
    }

    public function render(Environment $environment, AttributeInterface $attribute, $value): ?string
    {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions(
            array_map(
                fn (array $row): ColumnDefinition => $this->columnFactory->createFromNormalized($row),
                $attribute->getRawTableConfiguration()
            )
        );

        $table = $value->getData();
        Assert::isInstanceOf($table, Table::class);

        $header = sprintf('<tr>%s</tr>', join('', array_map(fn (ColumnCode $columnCode) => sprintf('<th>%s</th>', $columnCode->asString()), $tableConfiguration->columnCodes())));

        $body = '';
        foreach ($table as $row) {
            $line = '';
            foreach ($tableConfiguration->columnCodes() as $columnCode) {
                $cell = $row->cell($columnCode);
                if (null === $cell) {
                    $line .= '<td></td>';
                } else {
                    $normalizedCell = $cell->normalize();
                    if ($tableConfiguration->getColumnDataType($columnCode)->asString() === BooleanColumn::DATATYPE) {
                        $normalizedCell = $this->translator->trans($normalizedCell ? 'Yes' : 'No');
                    }
                    $line .= sprintf('<td>%s</td>', $normalizedCell);
                }
            }
            $body .= sprintf("<tr>%s</tr>\n", $line);
        }

        return sprintf("<table>\n<thead>%s</thead>\n<tbody>%s</tbody>\n</table>", $header, $body);
    }

    public function supportsAttributeType(string $attributeType): bool
    {
        return $attributeType === AttributeTypes::TABLE;
    }
}
