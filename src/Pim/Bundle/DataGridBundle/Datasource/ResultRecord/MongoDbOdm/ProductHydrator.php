<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\FieldsTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\ValuesTransformer;
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
        $locale  = $options['locale_code'];
        $scope   = $options['scope_code'];
        $config  = $options['attributes_configuration'];
        $groupId = $options['current_group_id'];

        $query   = $qb->hydrate(false)->getQuery();
        $results = $query->execute();

        $attributes = [];
        foreach ($config as $attributeConf) {
            $attributes[$attributeConf['id']]= $attributeConf;
        }

        $rows = [];
        $fieldsTransformer = new FieldsTransformer();
        $valuesTransformer = new ValuesTransformer();
        foreach ($results as $result) {
            $result = $fieldsTransformer->transform($result, $locale);
            $result = $valuesTransformer->transform($result, $attributes, $locale, $scope);
            $result = $this->prepareLinkedData($result, $locale, $scope, $groupId);

            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }

    /**
     * @param array   $result
     * @param string  $locale
     * @param string  $scope
     * @param integer $groupId
     *
     * @return array
     */
    protected function prepareLinkedData(array $result, $locale, $scope, $groupId)
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
                    $result['productLabel']= isset($attributeAsLabel[$backendType]) ?
                        $attributeAsLabel[$backendType] : null;
                } else {
                    $result['productLabel']= null;
                }
            }
        } else {
            $result['familyLabel']= '-';
        }

        if ($groupId && isset($result['groups'])) {
            $result['in_group']= in_array($groupId, $result['groups']);
        }

        return $result;
    }
}
