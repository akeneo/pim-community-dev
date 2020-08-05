<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatHeaderTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class AssociationIdentifierFlatHeaderTranslator implements FlatHeaderTranslatorInterface
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
        $associationsColumns = $this->associationColumnsResolver->resolveAssociationColumns();
        $quantifiedAssociationsIdentifierColumns = $this->associationColumnsResolver->resolveQuantifiedIdentifierAssociationColumns();

        return in_array($columnName, array_merge($associationsColumns, $quantifiedAssociationsIdentifierColumns));
    }

    public function translate(string $columnName, string $locale, HeaderTranslationContext $context)
    {
        list($associationType, $entityType) = explode('-', $columnName);
        $entityTypeLabelized =  $this->labelTranslator->translate(
            sprintf('pim_common.%s', $entityType),
            $locale,
            sprintf('[%s]', $entityType)
        );

        $associationTypeLabelized = $context->getAssociationTranslation($associationType)?: sprintf('[%s]', $associationType);

        return sprintf('%s %s', $associationTypeLabelized, $entityTypeLabelized);
    }
}
