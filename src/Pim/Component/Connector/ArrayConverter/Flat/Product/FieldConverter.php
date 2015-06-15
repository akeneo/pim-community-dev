<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product;

use Pim\Component\Connector\ArrayConverter\Flat\Product\Splitter\FieldSplitter;

/**
 * Converts a flat field to a structured one
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldConverter
{
    /** @var AssociationColumnsResolver */
    protected $assocFieldResolver;

    /** @var FieldSplitter */
    protected $fieldSplitter;

    /**
     * @param FieldSplitter              $fieldSplitter
     * @param AssociationColumnsResolver $assocFieldResolver
     */
    public function __construct(FieldSplitter $fieldSplitter, AssociationColumnsResolver $assocFieldResolver)
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
        $associationFields = $this->assocFieldResolver->resolveAssociationColumns();

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
        $associationFields = $this->assocFieldResolver->resolveAssociationColumns();

        $fields = array_merge(['categories', 'groups', 'enabled', 'family'], $associationFields);

        return in_array($column, $fields);
    }
}
