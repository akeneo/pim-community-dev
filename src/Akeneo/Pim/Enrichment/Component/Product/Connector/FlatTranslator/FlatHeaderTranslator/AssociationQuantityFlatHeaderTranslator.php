<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatHeaderTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class AssociationQuantityFlatHeaderTranslator implements FlatHeaderTranslatorInterface
{
    /**
     * @var AssociationColumnsResolver
     */
    private $associationColumnsResolver;

    /**
     * @var LabelTranslatorInterface
     */
    private $labelTranslator;

    public function __construct(AssociationColumnsResolver $associationColumnsResolver, LabelTranslatorInterface $labelTranslator)
    {
        $this->associationColumnsResolver = $associationColumnsResolver;
        $this->labelTranslator = $labelTranslator;
    }

    public function supports(string $columnName): bool
    {
        $quantifiedAssociationsQuantityColumns = $this->associationColumnsResolver->resolveQuantifiedQuantityAssociationColumns();

        return in_array($columnName, $quantifiedAssociationsQuantityColumns);
    }

    public function translate(string $columnName, string $locale, HeaderTranslationContext $context)
    {
        list($associationType, $entityType, $unit) = explode('-', $columnName);

        $associationTypeLabelized = $context->getAssociationTranslation($associationType)?: sprintf('[%s]', $associationType);
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
}
