<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\FieldsTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\ValuesTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\FamilyTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\CompletenessTransformer;
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
        $familyTransformer = new FamilyTransformer();
        $complTransformer = new CompletenessTransformer();

        foreach ($results as $result) {
            $result = $fieldsTransformer->transform($result, $locale);
            $result = $valuesTransformer->transform($result, $attributes, $locale, $scope);
            $result = $familyTransformer->transform($result, $locale);
            $result = $complTransformer->transform($result, $locale, $scope);

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

        if ($groupId && isset($result['groups'])) {
            $result['in_group']= in_array($groupId, $result['groups']);
        }

        return $result;
    }
}
