<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypeTranslations;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class AssociationTranslator implements FlatHeaderTranslatorInterface
{
    private array $associationTranslations = [];

    public function __construct(
        private readonly AssociationColumnsResolver $associationColumnsResolver,
        private readonly LabelTranslatorInterface $labelTranslator,
        private readonly GetAssociationTypeTranslations $getAssociationTypeTranslations
    ) {
    }

    public function supports(string $columnName): bool
    {
        $associationsColumns = $this->associationColumnsResolver->resolveAssociationColumns();
        $quantifiedAssociationsColumns = $this->associationColumnsResolver->resolveQuantifiedAssociationColumns();

        return in_array($columnName, array_merge(
            $associationsColumns,
            $quantifiedAssociationsColumns,
        ));
    }

    public function warmup(array $columnNames, string $locale): void
    {
        $associationTypes = $this->extractAssociationTypeCodes($columnNames);
        $quantifiedAssociationTypes = $this->extractQuantifiedAssociationTypeCodes($columnNames);
        $this->associationTranslations = $this->getAssociationTypeTranslations->byAssociationTypeCodeAndLocale(
            array_merge($associationTypes, $quantifiedAssociationTypes),
            $locale
        );
    }

    public function translate(string $columnName, string $locale): string
    {
        list($associationType, $entityType) = explode('-', $columnName);
        $entityTypeLabelized = $this->labelTranslator->translate(
            sprintf('pim_common.%s', $entityType),
            $locale,
            sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $entityType)
        );

        $associationTypeLabelized = $this->associationTranslations[$associationType] ??
            sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $associationType);

        $translation = sprintf('%s %s', $associationTypeLabelized, $entityTypeLabelized);

        if ($this->isQuantifiedAssociationQuantityColumn($columnName)) {
            $quantityLabel = $this->labelTranslator->translate(
                'pim_common.quantity',
                $locale,
                sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'quantity')
            );
            $translation = sprintf('%s %s', $translation, $quantityLabel);
        }

        return $translation;
    }

    private function extractAssociationTypeCodes(array $columnNames): array
    {
        $associationTypeCodes = [];
        foreach ($columnNames as $columnName) {
            if ($this->isAssociationColumn($columnName)) {
                list($associationType, $entityType) = explode('-', $columnName);

                $associationTypeCodes[] = $associationType;
            }
        }

        return array_unique($associationTypeCodes);
    }

    private function extractQuantifiedAssociationTypeCodes(array $columnNames): array
    {
        $quantifiedAssociationTypeCodes = [];
        foreach ($columnNames as $columnName) {
            if ($this->isQuantifiedAssociationIdentifierColumn($columnName)) {
                list($quantifiedAssociationType, $entityType) = explode('-', $columnName);

                $quantifiedAssociationTypeCodes[] = $quantifiedAssociationType;
            }
        }

        return array_unique($quantifiedAssociationTypeCodes);
    }

    private function isAssociationColumn(string $columnName): bool
    {
        $associationsColumns = $this->associationColumnsResolver->resolveAssociationColumns();

        return in_array($columnName, $associationsColumns);
    }

    private function isQuantifiedAssociationIdentifierColumn(string $columnName): bool
    {
        $identifierColumns = $this->associationColumnsResolver->resolveQuantifiedIdentifierAssociationColumns();

        return in_array($columnName, $identifierColumns);
    }

    private function isQuantifiedAssociationQuantityColumn(string $columnName): bool
    {
        $quantityColumns = $this->associationColumnsResolver->resolveQuantifiedQuantityAssociationColumns();

        return in_array($columnName, $quantityColumns);
    }
}
