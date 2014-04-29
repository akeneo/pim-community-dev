<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\FieldsTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\ValuesTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\FamilyTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\CompletenessTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\GroupsTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\AssociationTransformer;

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
    public function hydrate($qb, array $options = [])
    {
        $locale  = $options['locale_code'];
        $scope   = $options['scope_code'];
        $config  = $options['attributes_configuration'];
        $groupId = $options['current_group_id'];
        $associationTypeId = $options['association_type_id'];
        $currentProduct    = $options['current_product'];

        $query   = $qb->hydrate(false)->getQuery();
        $results = $query->execute();

        $attributes = [];
        foreach ($config as $attributeConf) {
            $attributes[$attributeConf['id']] = $attributeConf;
        }

        $rows = [];
        $fieldsTransformer = new FieldsTransformer();
        $valuesTransformer = new ValuesTransformer();
        $familyTransformer = new FamilyTransformer();
        $complTransformer  = new CompletenessTransformer();
        $groupsTransformer = new GroupsTransformer();
        $assocTramsformer  = new AssociationTransformer();

        foreach ($results as $result) {
            $result = $fieldsTransformer->transform($result, $locale);
            $result = $valuesTransformer->transform($result, $attributes, $locale, $scope);
            $result = $familyTransformer->transform($result, $locale);
            $result = $complTransformer->transform($result, $locale, $scope);
            $result = $groupsTransformer->transform($result, $locale, $groupId);
            $result = $assocTramsformer->transform($result, $associationTypeId, $currentProduct);

            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }
}
