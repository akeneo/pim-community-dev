<?php

namespace Akeneo\Pim\TableAttribute\Infrastructure\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer\ProductValueRenderer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\TableConfigurationFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Webmozart\Assert\Assert;

class TableProductValueRenderer implements ProductValueRenderer
{
    private TableConfigurationFactory $tableConfigurationFactory;
    private TranslatorInterface $translator;

    public function __construct(TableConfigurationFactory $tableConfigurationFactory, TranslatorInterface $translator)
    {
        $this->tableConfigurationFactory = $tableConfigurationFactory;
        $this->translator = $translator;
    }

    public function render(Environment $environment, AttributeInterface $attribute, ?ValueInterface $value, string $localeCode): ?string
    {
        if (null === $value) {
            return '';
        }

        $tableConfiguration = $this->tableConfigurationFactory->createFromNormalized(
            $attribute->getRawTableConfiguration()
        );

        $table = $value->getData();
        Assert::isInstanceOf($table, Table::class);

        $header = \sprintf(
            '<tr>%s</tr>',
            \implode(
                '',
                \array_map(
                    fn (ColumnCode $columnCode): string => sprintf('<th>%s</th>', $columnCode->asString()),
                    $tableConfiguration->columnCodes()
                )
            )
        );

        $body = '';
        foreach ($table as $row) {
            $line = '';
            foreach ($tableConfiguration->columnIds() as $columnId) {
                $cell = $row->cell($columnId);
                if (null === $cell) {
                    $line .= '<td></td>';
                } else {
                    $stringDataType = $tableConfiguration->getColumn($columnId)->dataType()->asString();
                    $normalizedCell = match ($stringDataType) {
                        BooleanColumn::DATATYPE => $this->translator->trans($cell->normalize() ? 'Yes' : 'No'),
                        MeasurementColumn::DATATYPE => $this->formatMeasurementValue($cell->normalize()),
                        default => $cell->normalize(),
                    };
                    /** @phpstan-ignore-next-line */
                    $line .= sprintf('<td>%s</td>', \twig_escape_filter($environment, $normalizedCell));
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

    private function formatMeasurementValue(mixed $normalizedCell): string
    {
        return \sprintf('%s %s', $normalizedCell['amount'] ?? '', $normalizedCell['unit'] ?? '');
    }
}
