<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;

/**
 * Hydrate results of Doctrine MongoDB query as ResultRecord array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductHydrator implements HydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function hydrate($qb, $options)
    {
        $locale = $options['locale_code'];
        $scope  = $options['scope_code'];
        $config = $options['attributes_configuration'];

        $query   = $qb->hydrate(false)->getQuery();
        $results = $query->execute();

        $attributes = [];
        foreach ($config as $attributeConf) {
            $attributes[$attributeConf['id']]= $attributeConf;
        }

        $rows = [];
        foreach ($results as $result) {
            $result = $this->prepareStaticData($result, $locale);
            $result = $this->prepareValuesData($result, $attributes, $locale, $scope);
            $result = $this->prepareLinkedData($result, $locale, $scope);

            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }

    /**
     * @param array  $result
     * @param string $locale
     *
     * @return array
     */
    protected function prepareStaticData(array $result, $locale)
    {
        $result['id']= $result['_id']->__toString();
        unset($result['_id']);
        $result['dataLocale']= $locale;
        $result['created']= isset($result['created']) ? $this->convertToDateTime($result['created']) : null;
        $result['updated']= isset($result['updated']) ? $this->convertToDateTime($result['updated']) : null;
        $result['enabled']= isset($result['enabled']) ? $result['enabled'] : false;

        return $result;
    }

    /**
     * @param array  $result
     * @param array  $attributes
     * @param string $locale
     * @param string $scope
     *
     * @return array
     */
    protected function prepareValuesData(array $result, array $attributes, $locale, $scope)
    {
        if (isset($result['values'])) {
            foreach ($result['values'] as $value) {
                $filterValueLocale = isset($value['locale']) && ($value['locale'] !== $locale);
                $filterValueScope = isset($value['scope']) && ($value['scope'] !== $scope);
                $attributeId = $value['attribute'];

                if (!$filterValueLocale && !$filterValueScope and isset($attributes[$attributeId])) {
                    $attribute = $attributes[$attributeId];
                    $attributeCode = $attribute['code'];
                    $value['attribute']= $attribute;
                    $result[$attributeCode]= $value;
                    $result[$attributeCode]= $this->prepareOptionsData($result, $attribute, $locale, $scope);
                    $result[$attributeCode]= $this->prepareDateData($result, $attribute);
                }
            }

            unset($result['values']);
        }

        return $result;
    }

    /**
     * @param array  $result
     * @param string $locale
     * @param string $scope
     *
     * @return array
     */
    protected function prepareLinkedData(array $result, $locale, $scope)
    {
        $normalizedData = $result[ProductQueryUtility::NORMALIZED_FIELD];

        $completenessCode = $scope.'-'.$locale;
        if (isset($normalizedData['completenesses'][$completenessCode])) {
            $result['ratio']= number_format($normalizedData['completenesses'][$completenessCode], 0);
        } else {
            $result['ratio'] = null;
        }

        if (isset($normalizedData['family'])) {
            $family = $normalizedData['family'];
            $result['familyLabel']= isset($family['label'][$locale]) ?
                $family['label'][$locale] : '['.$family['code'].']';
            if (isset($family['attributeAsLabel']) && $family['attributeAsLabel'] != null) {
                $attributeCode = $family['attributeAsLabel'];
                if (isset($result[$attributeCode])) {
                    $attributeAsLabel = $result[$attributeCode];
                    $backendType = $attributeAsLabel['attribute']['backendType'];
                    $result['productLabel']= $attributeAsLabel[$backendType];
                } else {
                    $result['productLabel'] = null;
                }
            }
        } else {
            $result['familyLabel'] = '-';
        }

        return $result;
    }

    /**
     * @param array  $result
     * @param array  $attribute
     * @param string $locale
     * @param string $scope
     *
     * @return array
     */
    protected function prepareOptionsData(array $result, array $attribute, $locale, $scope)
    {
        $attributeCode = $attribute['code'];
        $normalizedData = $result['normalizedData'];
        $fromNormData = array('pim_catalog_simpleselect', 'pim_catalog_multiselect');
        if (in_array($attribute['attributeType'], $fromNormData)) {
            $fieldCode = ProductQueryUtility::getNormalizedValueField(
                $attributeCode,
                $attribute['localizable'],
                $attribute['scopable'],
                $locale,
                $scope
            );
            $backendType = $attribute['backendType'];
            $options = $normalizedData[$fieldCode];

            if ($backendType === 'option') {
                $options = $this->filterOptionValues($options, $locale);
            } else {
                foreach ($options as $indexOption => $option) {
                    $options[$indexOption] = $this->filterOptionValues($option, $locale);
                }
            }

            $result[$attributeCode][$backendType]= $options;
        }

        return $result[$attributeCode];
    }

    /**
     * @param array $result
     * @param array $attribute
     *
     * @return array
     */
    protected function prepareDateData(array $result, array $attribute)
    {
        $attributeCode = $attribute['code'];
        $backendType = $attribute['backendType'];
        $value = $result[$attributeCode];

        if ($attribute['attributeType'] === 'pim_catalog_date' && isset($value[$backendType])) {
            $mongoDate = $value[$backendType];
            $value[$backendType]= $this->convertToDateTime($mongoDate);
        }

        return $value;
    }

    /**
     * @param array  $option
     * @param string $locale
     *
     * @return array $option
     */
    protected function filterOptionValues($option, $locale)
    {
        if (isset($option['optionValues'])) {
            foreach (array_keys($option['optionValues']) as $indexValue) {
                if ($indexValue !== $locale) {
                    unset($option['optionValues'][$indexValue]);
                }
            }
        }

        return $option;
    }

    /**
     * @param \MongoDate $mongoDate
     *
     * @return \DateTime
     */
    protected function convertToDateTime(\MongoDate $mongoDate)
    {
        $date = new \DateTime();
        $date->setTimestamp($mongoDate->sec);

        return $date;
    }
}
