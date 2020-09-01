<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Writer;

use Akeneo\Tool\Component\Connector\Writer\File\ColumnPresenterInterface;

class TranslatedColumnPresenter implements ColumnPresenterInterface
{
    public function present(array $columns, array $context): array
    {
        if (!$this->headerAreTranslated($context)) {
            return $columns;
        }

        $duplicatedTranslations = $this->findDuplicatedTranslations($columns);

        return $this->removeCodeWhenTranslationIsNotDuplicated($columns, $duplicatedTranslations);
    }

    private function findDuplicatedTranslations(array $columns)
    {
        $columnTranslations = array_map(function ($column) {
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

    private function removeCodeWhenTranslationIsNotDuplicated(array $columns, array $duplicatedTranslations)
    {
        return array_map(function ($column) use ($duplicatedTranslations) {
            $columnTranslation = $this->extractColumnTranslation($column);
            if (!in_array($columnTranslation, $duplicatedTranslations)) {
                return $columnTranslation;
            }

            return $column;
        }, $columns);
    }

    private function extractColumnTranslation(string $columnName)
    {
        return explode('--', $columnName, 2)[1];
    }
}
