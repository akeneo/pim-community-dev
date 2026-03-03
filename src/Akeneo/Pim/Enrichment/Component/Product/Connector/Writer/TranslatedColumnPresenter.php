<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Tool\Component\Connector\Writer\File\ColumnPresenterInterface;

class TranslatedColumnPresenter implements ColumnPresenterInterface
{
    public function present(array $columns, array $context): array
    {
        $columns = \array_combine($columns, $columns);
        if (!$this->headerAreTranslated($context)) {
            return $columns;
        }

        $duplicatedTranslations = $this->findDuplicatedTranslations($columns);

        return $this->removeCodeWhenTranslationIsNotDuplicated($columns, $duplicatedTranslations);
    }

    private function findDuplicatedTranslations(array $columns): array
    {
        $columnTranslations = array_map(function (string $column) {
            return $this->extractColumnTranslation($column);
        }, $columns);

        return array_unique(array_diff_assoc($columnTranslations, array_unique($columnTranslations)));
    }

    private function headerAreTranslated(array $context): bool
    {
        return
            isset($context['with_label'], $context['header_with_label']) &&
            $context['with_label'] &&
            $context['header_with_label'];
    }

    private function removeCodeWhenTranslationIsNotDuplicated(array $columns, array $duplicatedTranslations): array
    {
        return array_map(function (string $column) use ($duplicatedTranslations) {
            [$code, $columnTranslation] = explode(
                FlatTranslatorInterface::COLUMN_CODE_AND_TRANSLATION_SEPARATOR,
                $column,
                2
            );

            if (!in_array($columnTranslation, $duplicatedTranslations)) {
                return $columnTranslation;
            }

            return sprintf('%s - %s', $columnTranslation, $code);
        }, $columns);
    }

    private function extractColumnTranslation(string $columnName): string
    {
        return explode(FlatTranslatorInterface::COLUMN_CODE_AND_TRANSLATION_SEPARATOR, $columnName, 2)[1];
    }
}
