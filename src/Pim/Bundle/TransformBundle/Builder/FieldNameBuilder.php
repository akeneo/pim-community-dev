<?php

namespace Pim\Bundle\TransformBundle\Builder;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Create field names for associations and product values
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldNameBuilder
{
    /** @var static string */
    const CHANNEL_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Channel';

    /** @var static string */
    const LOCALE_CLASS  = 'Pim\Bundle\CatalogBundle\Entity\Locale';

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var string */
    protected $assocTypeClass;

    /** @var string */
    protected $attributeClass;

    /** @var string */
    protected $channelClass;

    /** @var string */
    protected $localeClass;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param string          $assocTypeClass
     * @param string          $attributeClass
     * @param string          $channelClass
     * @param string          $localeClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        $assocTypeClass, $attributeClass,
        $channelClass = self::CHANNEL_CLASS,
        $localeClass = self::LOCALE_CLASS
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->assocTypeClass  = $assocTypeClass;
        $this->attributeClass  = $attributeClass;
        $this->channelClass    = $channelClass;
        $this->localeClass     = $localeClass;
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
            $fieldNames[] = $assocType->getCode().'-groups';
            $fieldNames[] = $assocType->getCode().'-products';
        }

        return $fieldNames;
    }

    /**
     * Extract attribute field name informations with attribute code, locale code, scope code
     * and optionally price currency
     *
     * Returned array like:
     * [
     *     "attribute"   => AbstractAttribute,
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
            $attributeInfos = $this->extractAttributeInfos($attribute, $explodedFieldName);
            $this->checkFieldNameLocaleByChannel($attribute,  $fieldName, $attributeInfos);

            return $attributeInfos;
        }

        return null;
    }

    /**
     * Extract informations from an attribute and exploded field name
     * This method is used from extractAttributeFieldNameInfos and can be redefine to add new rules
     *
     * @param AbstractAttribute $attribute
     * @param array             $explodedFieldName
     *
     * @return array
     */
    protected function extractAttributeInfos(AbstractAttribute $attribute, array $explodedFieldName)
    {
        if ($attribute->isLocalizable() && $attribute->isScopable()) {
            $localeCode = $explodedFieldName[1];
            $scopeCode  = $explodedFieldName[2];
            $priceCurrency = $attribute->getBackendType() === 'prices' ? $explodedFieldName[3] : null;
        } elseif ($attribute->isLocalizable()) {
            $localeCode = $explodedFieldName[1];
            $scopeCode  = null;
            $priceCurrency = $attribute->getBackendType() === 'prices' ? $explodedFieldName[2] : null;
        } elseif ($attribute->isScopable()) {
            $localeCode = null;
            $scopeCode  = $explodedFieldName[1];
            $priceCurrency = $attribute->getBackendType() === 'prices' ? $explodedFieldName[2] : null;
        } else {
            $localeCode = null;
            $scopeCode  = null;
            $priceCurrency = $attribute->getBackendType() === 'prices' ? $explodedFieldName[1] : null;
        }

        $priceArray = (null === $priceCurrency) ? [] : ['price_currency' => $priceCurrency];

        return [
            'attribute'   => $attribute,
            'locale_code' => $localeCode,
            'scope_code'  => $scopeCode,
        ] + $priceArray;
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
     * @param AbstractAttribute $attribute
     * @param string            $fieldName
     * @param array             $explodedFieldName
     *
     * @throws \InvalidArgumentException
     */
    protected function checkFieldNameTokens(AbstractAttribute $attribute, $fieldName, array $explodedFieldName)
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
     * Check the consistency of the field with channel associated
     *
     * @param AbstractAttribute $attribute
     * @param string            $fieldName
     * @param array             $attributeInfos
     *
     * @throws \InvalidArgumentException
     */
    protected function checkFieldNameLocaleByChannel(AbstractAttribute $attribute,  $fieldName, array $attributeInfos)
    {
        if ($attribute->isScopable() &&
            $attribute->isLocalizable() &&
            isset($attributeInfos['scope_code']) &&
            isset($attributeInfos['locale_code'])
        ) {
            $channel = $this->getRepository($this->channelClass)->findByReference($attributeInfos['scope_code']);
            $locale = $this->getRepository($this->localeClass)->findByReference($attributeInfos['locale_code']);

            if ($channel !== null && $locale !== null && !$channel->hasLocale($locale)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The locale "%s" of the field "%s" is not available in scope "%s"',
                        $attributeInfos['locale_code'],
                        $fieldName,
                        $attributeInfos['scope_code']
                    )
                );
            }
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
