<?php

namespace Pim\Bundle\TransformBundle\Builder;

use Pim\Component\Connector\ArrayConverter\Flat\Product\AssociationColumnsResolver;
use Pim\Component\Connector\ArrayConverter\Flat\Product\AttributeColumnInfoExtractor;

/**
 * Create field names for associations and product values
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.5
 */
class FieldNameBuilder
{
    /** @var AssociationColumnsResolver*/
    protected $associationColumnsResolver;

    /** @var AttributeColumnInfoExtractor*/
    protected $attributeFieldExtractor;

    /**
     * @param AssociationColumnsResolver   $associationColumnsResolver
     * @param AttributeColumnInfoExtractor $attributeFieldExtractor
     */
    public function __construct(
        AssociationColumnsResolver $associationColumnsResolver,
        AttributeColumnInfoExtractor $attributeFieldExtractor
    ) {
        $this->associationColumnsResolver = $associationColumnsResolver;
        $this->attributeFieldExtractor    = $attributeFieldExtractor;
    }

    /**
     * Get the association field names
     *
     * @return array
     */
    public function getAssociationFieldNames()
    {
        return $this->associationColumnsResolver->resolveAssociationColumns();
    }

    /**
     * Extract attribute field name information with attribute code, locale code, scope code
     * and optionally price currency
     *
     * Returned array like:
     * [
     *     "attribute"   => AttributeInterface,
     *     "locale_code" => <locale_code>|null,
     *     "scope_code"  => <scope_code>|null,
     *     "price_currency" => <currency_code> // this key is optional
     * ]
     *
     * Return null if the field name does not match an attribute.
     *
     * @param string $fieldName
     *
     * @return array|null
     */
    public function extractAttributeFieldNameInfos($fieldName)
    {
        return $this->attributeFieldExtractor->extractColumnInfo($fieldName);
    }
}
