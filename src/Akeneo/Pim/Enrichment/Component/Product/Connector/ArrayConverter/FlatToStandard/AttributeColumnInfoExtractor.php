<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Extracts attribute field information
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeColumnInfoExtractor
{
    const ARRAY_SEPARATOR = ',';
    const FIELD_SEPARATOR = '-';
    const UNIT_SEPARATOR = ' ';

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /** @var AssociationColumnsResolver */
    protected $assoColumnResolver;

    /** @var array */
    protected $fieldNameInfoCache;

    /** @var array */
    protected $excludedFieldNames;

    /**
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param AssociationColumnsResolver            $assoColumnResolver
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        AssociationColumnsResolver $assoColumnResolver = null
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->channelRepository   = $channelRepository;
        $this->localeRepository    = $localeRepository;
        $this->assoColumnResolver  = $assoColumnResolver;
        $this->fieldNameInfoCache  = [];
        $this->excludedFieldNames  = [];
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
    public function extractColumnInfo($fieldName)
    {
        if ($this->assoColumnResolver &&
            in_array($fieldName, $this->assoColumnResolver->resolveAssociationColumns())
        ) {
            $this->excludedFieldNames[] = $fieldName;
        }

        if (!isset($this->fieldNameInfoCache[$fieldName]) && !in_array($fieldName, $this->excludedFieldNames)) {
            $explodedFieldName = explode(self::FIELD_SEPARATOR, $fieldName);
            $attributeCode = $explodedFieldName[0];
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            if (null !== $attribute) {
                $this->checkFieldNameTokens($attribute, $fieldName, $explodedFieldName);
                $attributeInfo = $this->extractAttributeInfo($attribute, $explodedFieldName);
                $this->checkFieldNameLocaleByChannel($attribute, $fieldName, $attributeInfo);
                $this->fieldNameInfoCache[$fieldName] = $attributeInfo;
            } else {
                $this->excludedFieldNames[] = $fieldName;
            }
        }

        return isset($this->fieldNameInfoCache[$fieldName]) ? $this->fieldNameInfoCache[$fieldName] : null;
    }

    /**
     * Extract information from an attribute and exploded field name
     * This method is used from extractColumnInfo and can be redefine to add new rules
     *
     * @param AttributeInterface $attribute
     * @param array              $explodedFieldName
     *
     * @return array
     */
    protected function extractAttributeInfo(AttributeInterface $attribute, array $explodedFieldName)
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
        $isLocalizable = $attribute->isLocalizable();
        $isScopable = $attribute->isScopable();
        $isPrice = 'prices' === $attribute->getBackendType();

        $expectedSize = $this->calculateExpectedSize($attribute);

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
     * Calculates the expected size of the field with the attribute and its properties locale, scope, etc.
     *
     * @param AttributeInterface $attribute
     *
     * @return int
     */
    protected function calculateExpectedSize(AttributeInterface $attribute)
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

        return $expectedSize;
    }

    /**
     * Check the consistency of the field with channel associated
     *
     * @param AttributeInterface $attribute
     * @param string             $fieldName
     * @param array              $attributeInfo
     *
     * @throws \InvalidArgumentException
     */
    protected function checkFieldNameLocaleByChannel(AttributeInterface $attribute, $fieldName, array $attributeInfo)
    {
        if ($attribute->isScopable() &&
            $attribute->isLocalizable() &&
            isset($attributeInfo['scope_code']) &&
            isset($attributeInfo['locale_code'])
        ) {
            $channel = $this->channelRepository->findOneByIdentifier($attributeInfo['scope_code']);
            $locale = $this->localeRepository->findOneByIdentifier($attributeInfo['locale_code']);

            if ($channel !== null && $locale !== null && !$channel->hasLocale($locale)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The locale "%s" of the field "%s" is not available in scope "%s"',
                        $attributeInfo['locale_code'],
                        $fieldName,
                        $attributeInfo['scope_code']
                    )
                );
            }
        }
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
            $attributeInfo = $this->extractAttributeInfo($attribute, $explodedFieldNames);
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
}
