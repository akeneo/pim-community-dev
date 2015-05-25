<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product\Converter;

use Pim\Component\Connector\ArrayConverter\Flat\Product\Splitter\FieldSplitter;
use Pim\Component\Connector\ArrayConverter\Flat\ProductAssociationFieldResolver;

/**
 * Converts a flat field to a structured one
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFieldConverter
{
    /** @var ProductAssociationFieldResolver */
    protected $assocFieldResolver;

    /** @var FieldSplitter */
    protected $fieldSplitter;

    /**
     * @param FieldSplitter                   $fieldSplitter
     * @param ProductAssociationFieldResolver $assocFieldResolver
     */
    public function __construct(FieldSplitter $fieldSplitter, ProductAssociationFieldResolver $assocFieldResolver)
    {
        $this->assocFieldResolver = $assocFieldResolver;
        $this->fieldSplitter      = $fieldSplitter;
    }

    /**
     * Converts a flat field to a structured one
     *
     * @param string $column
     * @param string $value
     *
     * @throws \LogicException
     *
     * @return array
     */
    public function convert($column, $value)
    {
        $associationFields = $this->assocFieldResolver->resolveAssociationFields();

        if (in_array($column, $associationFields)) {
            $value = $this->fieldSplitter->splitCollection($value);
            list($associationTypeCode, $associatedWith) = $this->fieldSplitter->splitFieldName($column);

            return ['associations' => [$associationTypeCode => [$associatedWith => $value]]];
        } elseif (in_array($column, ['categories', 'groups'])) {
            return [$column => $this->fieldSplitter->splitCollection($value)];
        } elseif ('enabled' === $column) {
            return [$column => (bool) $value];
        } elseif ('family' === $column) {
            return [$column => $value];
        }

        throw new \LogicException(sprintf('No converters found for attribute type "%s"', $column));
    }

    /**
     * @param string $column
     *
     * @return bool
     */
    public function supportsColumn($column)
    {
        $associationFields = $this->assocFieldResolver->resolveAssociationFields();

        $fields = array_merge(['categories', 'groups', 'enabled', 'family'], $associationFields);

        return in_array($column, $fields);
    }
}
