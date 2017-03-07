<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\Orm;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Hydrate results of Doctrine ORM query as ResultRecord array
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
    public function hydrate($qb, array $options = [])
    {
        $localeCode = $options['locale_code'];
        $channelCode = $options['scope_code'];

        $query = $qb->getQuery();
        $results = $query->getArrayResult();

        $rows = [];
        foreach ($results as $result) {
            $entityFields = [];
            if (isset($result[0])) {
                $entityFields = $result[0];
                unset($result[0]);
            }
            $otherFields = $result;
            $result = $entityFields + $otherFields;
            // TODO - TIP-664: make the datagrid work with ES
            $result['productLabel'] = $this->retrieveLabel(
                $result,
                $result['attributeCodeAsLabel'],
                $channelCode,
                $localeCode
            );
            $result = $this->prepareValues($result, $channelCode, $localeCode);
            $result = $this->prepareGroups($result);
            $result['dataLocale'] = $localeCode;
            unset($result['rawValues']);

            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }

    /**
     * @param array  $rawProduct
     * @param string $attributeAsLabel
     * @param string $channelCode
     * @param string $localeCode
     *
     * @return string
     */
    protected function retrieveLabel(array $rawProduct, $attributeAsLabel, $channelCode, $localeCode)
    {
        $rawValues = $rawProduct['rawValues'];

        $label = $this->getValueEventuallyByLocaleAndScope(
            $rawValues,
            $attributeAsLabel,
            $channelCode,
            $localeCode
        );

        if (null !== $label) {
            return $label;
        }

        return $rawProduct['identifier'];
    }

    /**
     * Prepare product values
     *
     * @param array $result
     *
     * @return array
     */
    protected function prepareValues($result)
    {
        if (isset($result['values'])) {
            $values = $result['values'];
            foreach ($values as $value) {
                $result[$value['attribute']['code']] = $value;
            }
            unset($result['values']);
        }

        return $result;
    }

    /**
     * Prepare product groups
     *
     * @param array $result
     *
     * @return array
     */
    protected function prepareGroups($result)
    {
        if (isset($result['groups'])) {
            $groups = [];
            foreach ($result['groups'] as $group) {
                $code = $group['code'];
                $label = (count($group['translations']) > 0) ? $group['translations'][0]['label'] : null;
                $groups[$code] = ['code' => $code, 'label' => $label];
            }
            $result['groups'] = $groups;
        }

        return $result;
    }

    /**
     * @param array  $rawValues
     * @param string $attributeCode
     * @param string $channelCode
     * @param string $localeCode
     *
     * @return string
     */
    private function getValueEventuallyByLocaleAndScope(array $rawValues, $attributeCode, $channelCode, $localeCode)
    {
        if (isset($rawValues[$attributeCode]['<all_channels>']['<all_locales>'])) {
            return $rawValues[$attributeCode]['<all_channels>']['<all_locales>'];
        }

        if (null !== $channelCode && null !== $localeCode && isset($rawValues[$attributeCode][$channelCode][$localeCode])) {
            return $rawValues[$attributeCode][$channelCode][$localeCode];
        }

        if (null !== $channelCode && isset($rawValues[$attributeCode][$channelCode]['<all_locales>'])) {
            return $rawValues[$attributeCode][$channelCode]['<all_locales>'];
        }

        if (null !== $localeCode && isset($rawValues[$attributeCode]['<all_channels>'][$localeCode])) {
            return $rawValues[$attributeCode]['<all_channels>'][$localeCode];
        }

        return null;
    }
}
