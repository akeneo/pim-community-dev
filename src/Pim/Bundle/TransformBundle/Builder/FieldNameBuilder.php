<?php

namespace Pim\Bundle\TransformBundle\Builder;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Create field names for associations and product values
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldNameBuilder
{
    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var string */
    protected $assocTypeClass;

    /** @var string */
    protected $attributeClass;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param string          $assocTypeClass
     * @param string          $attributeClass
     */
    public function __construct(ManagerRegistry $managerRegistry, $assocTypeClass, $attributeClass)
    {
        $this->managerRegistry = $managerRegistry;
        $this->assocTypeClass  = $assocTypeClass;
        $this->attributeClass  = $attributeClass;
    }

    /**
     * Get the association field names
     *
     * @return array
     */
    public function getAssociationFieldNames()
    {
        $fieldNames = [];
        $assocTypes = $this->getRepository($this->assocTypeClass)->findAll();
        foreach ($assocTypes as $assocType) {
            $fieldNames[] = $assocType->getCode() .'-groups';
            $fieldNames[] = $assocType->getCode() .'-products';
        }

        return $fieldNames;
    }

    /**
     * Extract attribute field name informations with attribute code, locale code, scope code
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
        $explodedFieldName = explode("-", $fieldName);
        $attributeCode = $explodedFieldName[0];
        $attribute = $this->getRepository($this->attributeClass)->findByReference($attributeCode);

        if (null !== $attribute) {
            $this->checkFieldNameTokens($attribute, $fieldName, $explodedFieldName);

            return $this->extractAttributeInfos($attribute, $explodedFieldName);
        }

        return null;
    }

    /**
     * Extract informations from an attribute and exploded field name
     * This method is used from extractAttributeFieldNameInfos and can be redefine to add new rules
     *
     * @param AttributeInterface $attribute
     * @param array              $explodedFieldName
     *
     * @return array
     */
    protected function extractAttributeInfos(AttributeInterface $attribute, array $explodedFieldName)
    {
        array_shift($explodedFieldName);

        $info = [
            'attribute'   => $attribute,
            'locale_code' => $attribute->isLocalizable() ? array_shift($explodedFieldName) : null,
            'scope_code'  => $attribute->isScopable() ? array_shift($explodedFieldName) : null,
        ];

        if ('prices' === $attribute->getBackendType()) {
            $info['price_currency'] = array_shift($explodedFieldName);
        }

        return $info;
    }

    /**
     * Extract field name informations from a potential association field name
     *
     * Returned array like:
     * [
     *     "assoc_type_code"   => <assoc_type_code>,
     *     "part" => "groups"|"products",
     * ]
     *
     * @param string $fieldName
     *
     * @return string[]|null
     */
    public function extractAssociationFieldNameInfos($fieldName)
    {
        $matches = [];
        $regex = '/^([a-zA-Z0-9_]+)-(groups|products)$/';
        if (preg_match($regex, $fieldName, $matches)) {
            return ['assoc_type_code' => $matches[1], 'part' => $matches[2]];
        }
    }

    /**
     * Check the consistency of the field with the attribute and it properties locale, scope, currency
     *
     * @param AttributeInterface $attribute
     * @param string             $fieldName
     * @param array              $explodedFieldName
     *
     * @throws \InvalidArgumentException
     */
    protected function checkFieldNameTokens(AttributeInterface $attribute, $fieldName, array $explodedFieldName)
    {
        // the expected number of tokens in a field may vary,
        //  - with the current price import, the currency can be optionaly present in the header,
        //  - with the current metric import, a "-unit" field can be added in the header,
        //
        // To avoid BC break, we keep the support in this fix, a next minor version could contain only the
        // support of currency code in the header and metric in a single field
        $expectedSize = [0];
        $isLocalizable = $attribute->isLocalizable();
        $isScopable = $attribute->isScopable();
        $isPrice = 'prices' === $attribute->getBackendType();
        $isMetric = 'metric' === $attribute->getBackendType();
        if ($isLocalizable && $isScopable && $isPrice) {
            $expectedSize = [3, 4];
        } elseif ($isLocalizable && $isScopable && $isMetric) {
            $expectedSize = [3, 4];
        } elseif ($isLocalizable && $isScopable) {
            $expectedSize = [3];
        } elseif ($isLocalizable && $isPrice) {
            $expectedSize = [2, 3];
        } elseif ($isScopable && $isPrice) {
            $expectedSize = [2, 3];
        } elseif ($isLocalizable && $isMetric) {
            $expectedSize = [2, 3];
        } elseif ($isScopable && $isMetric) {
            $expectedSize = [2, 3];
        } elseif ($isLocalizable) {
            $expectedSize = [2];
        } elseif ($isScopable) {
            $expectedSize = [2];
        } elseif ($isPrice) {
            $expectedSize = [1, 2];
        } elseif ($isMetric) {
            $expectedSize = [1, 2];
        } else {
            $expectedSize = [1];
        }

        $nbTokens = count($explodedFieldName);
        if (!in_array($nbTokens, $expectedSize)) {
            $expected = [
                $isLocalizable ? 'a locale' : 'no locale',
                $isScopable ? 'a scope' : 'no scope',
                $isPrice ? 'an optional currency' : 'no currency',
            ];
            $expected = implode($expected, ', ');

            throw new \InvalidArgumentException(
                sprintf(
                    'The field "%s" is not well-formated, attribute "%s" expects %s',
                    $fieldName,
                    $attribute->getCode(),
                    $expected
                )
            );
        }
    }

    /**
     * @param string $entityClass
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($entityClass)
    {
        return $this->managerRegistry->getRepository($entityClass);
    }
}
