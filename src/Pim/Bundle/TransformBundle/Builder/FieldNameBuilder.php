<?php

namespace Pim\Bundle\TransformBundle\Builder;

use Pim\Component\Connector\ArrayConverter\Flat\Product\Extractor\ProductAttributeFieldExtractor;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Resolver\AssociationFieldsResolver;

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
    /** @var AssociationFieldsResolver*/
    protected $associationFieldsResolver;

    /** @var ProductAttributeFieldExtractor*/
    protected $attributeFieldExtractor;

    /**
     * @param AssociationFieldsResolver      $associationFieldsResolver
     * @param ProductAttributeFieldExtractor $attributeFieldExtractor
     */
    public function __construct(
        AssociationFieldsResolver $associationFieldsResolver,
        ProductAttributeFieldExtractor $attributeFieldExtractor
    ) {
        $this->associationFieldsResolver = $associationFieldsResolver;
        $this->attributeFieldExtractor = $attributeFieldExtractor;
    }

    /**
     * Get the association field names
     *
     * @return array
     */
    public function getAssociationFieldNames()
    {
        return $this->associationFieldsResolver->resolveAssociationFields();
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
        return $this->attributeFieldExtractor->extractAttributeFieldNameInfos($fieldName);
    }
}
