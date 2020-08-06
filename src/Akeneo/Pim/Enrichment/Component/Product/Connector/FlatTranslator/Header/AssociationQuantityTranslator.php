<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Association\GetAssociationTypeTranslations;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;


class AssociationQuantityTranslator implements FlatHeaderTranslatorInterface
{
    /**
     * @var AssociationColumnsResolver
     */
    private $associationColumnsResolver;

    /**
     * @var LabelTranslatorInterface
     */
    private $labelTranslator;

    /**
     * @var GetAssociationTypeTranslations
     */
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
        $quantifiedAssociationsQuantityColumns = $this->associationColumnsResolver->resolveQuantifiedQuantityAssociationColumns();

        return in_array($columnName, $quantifiedAssociationsQuantityColumns);
    }

    public function warmup(array $columnNames, string $locale): void
    {
        $associationTypes = $this->extractAssociationTypeCodes($flatItemsByColumnName);
        $this->associationTranslations = $this->getAssociationTypeTranslations->byAssociationTypeCodeAndLocale(
            array_merge($associationTypes, $quantifiedAssociationTypes),
            $locale
        );
    }

    public function translate(string $columnName, string $locale)
    {
        list($associationType, $entityType, $unit) = explode('-', $columnName);

        $associationTypeLabelized = $this->associationTranslations[$associationType] ?: sprintf('[%s]', $associationType);
        $entityTypeLabelized =  $this->labelTranslator->translate(
            sprintf('pim_common.%s', $entityType),
            $locale,
            sprintf('[%s]', $entityType)
        );

        $unitLabelized =  $this->labelTranslator->translate(
            'pim_common.unit',
            $locale,
            '([unit])'
        );

        return sprintf('%s %s %s', $associationTypeLabelized, $entityTypeLabelized, $unitLabelized);
    }

    private function extractAssociationTypeCodes(array $columnNames): array
    {
        $associationTypeCodes = [];
        foreach ($columnNames as $columnName) {
            if ($this->isAssociationColumn($columnName)) {
                list($quantifiedAssociationType, $entityType) = explode('-', $columnName);

                $associationTypeCodes[] = $quantifiedAssociationType;
            }
        }

        return array_unique($associationTypeCodes);
    }

    private function isAssociationColumn(string $column): bool
    {
        $associationsColumns = $this->associationColumnsResolver->resolveAssociationColumns();

        return in_array($column, $associationsColumns);
    }
}
