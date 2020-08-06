<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypeTranslations;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class AssociationTranslator implements FlatHeaderTranslatorInterface
{
    /** @var AssociationColumnsResolver */
    private $associationColumnsResolver;

    /** @var LabelTranslatorInterface */
    private $labelTranslator;

    /** @var GetAssociationTypeTranslations */
    private $getAssociationTypeTranslations;

    private $associationTranslations;

    public function __construct(
        AssociationColumnsResolver $associationColumnsResolver,
        LabelTranslatorInterface $labelTranslator,
        GetAssociationTypeTranslations $getAssociationTypeTranslations
    ) {
        $this->associationColumnsResolver = $associationColumnsResolver;
        $this->labelTranslator = $labelTranslator;
        $this->getAssociationTypeTranslations = $getAssociationTypeTranslations;
    }

    public function supports(string $columnName): bool
    {
        $associationsColumns = $this->associationColumnsResolver->resolveAssociationColumns();
        $quantifiedAssociationsIdentifierColumns = $this->associationColumnsResolver->resolveQuantifiedIdentifierAssociationColumns();
        $quantifiedAssociationsQuantityColumns = $this->associationColumnsResolver->resolveQuantifiedQuantityAssociationColumns();

        return in_array($columnName, array_merge(
            $associationsColumns,
            $quantifiedAssociationsIdentifierColumns,
            $quantifiedAssociationsQuantityColumns
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
            sprintf('[%s]', $entityType)
        );

        $associationTypeLabelized = isset($this->associationTranslations[$associationType]) ?
            $this->associationTranslations[$associationType] : sprintf('[%s]', $associationType);

        $translation = sprintf('%s %s', $associationTypeLabelized, $entityTypeLabelized);

        if ($this->isQuantifiedAssociationQuantityColumn($columnName)) {
            $quantityLabel = $this->labelTranslator->translate(
                'pim_common.quantity',
                $locale,
                '[quantity]'
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

    private function extractQuantifiedAssociationTypeCodes(array $flatItemsByColumnName): array
    {
        $quantifiedAssociationTypeCodes = [];
        foreach ($flatItemsByColumnName as $columnName => $flatItemValues) {
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
        $quantifiedAssociationsColumns = $this->associationColumnsResolver->resolveQuantifiedIdentifierAssociationColumns();

        return in_array($columnName, $quantifiedAssociationsColumns);
    }

    private function isQuantifiedAssociationQuantityColumn(string $columnName): bool
    {
        $quantityColumns = $this->associationColumnsResolver->resolveQuantifiedQuantityAssociationColumns();

        return in_array($columnName, $quantityColumns);
    }
}
