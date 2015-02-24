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
    const ARRAY_SEPARATOR            = ',';
    const FIELD_SEPARATOR            = '-';
    const UNIT_SEPARATOR             = ' ';
    const GROUP_ASSOCIATION_SUFFIX   = '-groups';
    const PRODUCT_ASSOCIATION_SUFFIX = '-products';

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
        $assocTypeClass,
        $attributeClass,
        $channelClass,
        $localeClass
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
            $fieldNames[] = $assocType->getCode() . self::GROUP_ASSOCIATION_SUFFIX;
            $fieldNames[] = $assocType->getCode() . self::PRODUCT_ASSOCIATION_SUFFIX;
        }

        return $fieldNames;
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
        $explodedFieldName = explode(self::FIELD_SEPARATOR, $fieldName);
        $attributeCode = $explodedFieldName[0];
        $repository = $this->getRepository($this->attributeClass);
        $attribute = $repository->findOneByIdentifier($attributeCode);

        if (null !== $attribute) {
            $this->checkFieldNameTokens($attribute, $fieldName, $explodedFieldName);
            $attributeInfos = $this->extractAttributeInfos($attribute, $explodedFieldName);
            $this->checkFieldNameLocaleByChannel($attribute, $fieldName, $attributeInfos);

            return $attributeInfos;
        }

        return null;
    }

    /**
     * Extract information from an attribute and exploded field name
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
        } elseif ('metric' === $attribute->getBackendType()) {
            $info['metric_unit'] = array_shift($explodedFieldName);
        }

        return $info;
    }

    /**
     * Extract field name information from a potential association field name
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
        //  - with the current price import, the currency can be optionally present in the header,
        //  - with the current metric import, a "-unit" field can be added in the header,
        //
        // To avoid BC break, we keep the support in this fix, a next minor version could contain only the
        // support of currency code in the header and metric in a single field
        $isLocalizable = $attribute->isLocalizable();
        $isScopable = $attribute->isScopable();
        $isPrice = 'prices' === $attribute->getBackendType();
        $isMetric = 'metric' === $attribute->getBackendType();

        $expectedSize = 1;
        $expectedSize = $isLocalizable ? $expectedSize + 1 : $expectedSize;
        $expectedSize = $isScopable ? $expectedSize + 1 : $expectedSize;

        if ($isMetric || $isPrice) {
            $expectedSize = [$expectedSize, $expectedSize + 1];
        } else {
            $expectedSize = [$expectedSize];
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
                    'The field "%s" is not well-formatted, attribute "%s" expects %s',
                    $fieldName,
                    $attribute->getCode(),
                    $expected
                )
            );
        }
        if ($isLocalizable) {
            $this->checkForLocaleSpecificValue($attribute, $explodedFieldName);
        }
    }

    /**
     * Check the consistency of the field with channel associated
     *
     * @param AttributeInterface $attribute
     * @param string             $fieldName
     * @param array              $attributeInfos
     *
     * @throws \InvalidArgumentException
     */
    protected function checkFieldNameLocaleByChannel(AttributeInterface $attribute, $fieldName, array $attributeInfos)
    {
        if ($attribute->isScopable() &&
            $attribute->isLocalizable() &&
            isset($attributeInfos['scope_code']) &&
            isset($attributeInfos['locale_code'])
        ) {
            $channelRepository = $this->getRepository($this->channelClass);
            $localeRepository = $this->getRepository($this->localeClass);

            $channel = $channelRepository->findOneByIdentifier($attributeInfos['scope_code']);
            $locale = $localeRepository->findOneByIdentifier($attributeInfos['locale_code']);

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

    /**
     * Check if provided locales for an locale specific attribute exist
     *
     * @param AttributeInterface $attribute
     * @param array              $explodedFieldNames
     */
    protected function checkForLocaleSpecificValue(AttributeInterface $attribute, array $explodedFieldNames)
    {
        if ($attribute->isLocaleSpecific()) {
            $attributeInfo = $this->extractAttributeInfos($attribute, $explodedFieldNames);
            $availableLocales = $attribute->getLocaleSpecificCodes();
            if (!in_array($explodedFieldNames[1], $availableLocales)) {
                throw new \LogicException(
                    sprintf(
                        'The provided specific locale "%s" does not exist for "%s" attribute ',
                        $attributeInfo['locale_code'],
                        $attribute->getCode()
                    )
                );
            }
        }
    }

    /**
     * Split a collection in a flat value :
     *
     * '10 EUR, 24 USD' => ['10 EUR', '24 USD']
     *
     * @param string $value Raw value
     *
     * @return array
     */
    public static function splitCollection($value)
    {
        return '' === $value ? [] : explode(self::ARRAY_SEPARATOR, $value);
    }

    /**
     * Split a field name:
     * 'description-en_US-mobile' => ['description', 'en_US', 'mobile']
     *
     * @param string $field Raw field name
     *
     * @return array
     */
    public static function splitFieldName($field)
    {
        return '' === $field ? [] : explode(self::FIELD_SEPARATOR, $field);
    }

    /**
     * Split a value with it's unit/currency:
     * '10 EUR'   => ['10', 'EUR']
     * '10 METER' => ['10', 'METER']
     *
     * @param string $value Raw value
     *
     * @return array
     */
    public static function splitUnitValue($value)
    {
        return '' === $value ? [] : explode(self::UNIT_SEPARATOR, $value);
    }
}
