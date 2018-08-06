<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ConvertedField;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;

/**
 * Converts a flat product model field to a structured format
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldConverter implements FieldConverterInterface
{
    /** @var FieldSplitter */
    private $fieldSplitter;

    /** @var AssociationColumnsResolver */
    private $assocFieldResolver;

    private const PRODUCT_MODEL_FIELDS = ['parent', 'code', 'family_variant', 'categories'];

    public function __construct(
        FieldSplitter $fieldSplitter,
        AssociationColumnsResolver $assocFieldResolver
    ) {
        $this->fieldSplitter = $fieldSplitter;
        $this->assocFieldResolver = $assocFieldResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(string $fieldName, $value): ConvertedField
    {
        $associationFields = $this->assocFieldResolver->resolveAssociationColumns();

        if (in_array($fieldName, $associationFields)) {
            $value = $this->fieldSplitter->splitCollection($value);
            list($associationTypeCode, $associatedWith) = $this->fieldSplitter->splitFieldName($fieldName);

            return new ConvertedField('associations', [$associationTypeCode => [$associatedWith => $value]]);
        }

        if ('categories' === $fieldName) {
            $categories = $this->fieldSplitter->splitCollection($value);

            return new ConvertedField($fieldName, $categories);
        }

        // Code must be alpha-numeric
        if (in_array($fieldName, ['parent', 'code', 'family_variant'])) {
            return new ConvertedField($fieldName, (string) $value);
        }

        return new ConvertedField($fieldName, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsColumn($fieldName): bool
    {
        $associationFields = $this->assocFieldResolver->resolveAssociationColumns();
        $fields = array_merge(self::PRODUCT_MODEL_FIELDS, $associationFields);

        return in_array($fieldName, $fields);
    }
}
