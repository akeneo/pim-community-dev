<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\CompletenessTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\FamilyTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\FieldsTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\GroupsTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\ValuesTransformer;

/**
 * Hydrate results of Doctrine MongoDB query as ResultRecord array
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociatedProductHydrator implements HydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function hydrate($qb, array $options = [])
    {
        $locale = $options['locale_code'];
        $scope = $options['scope_code'];
        $config = $options['attributes_configuration'];
        $groupId = $options['current_group_id'];
        $associationTypeId = $options['association_type_id'];
        $currentProduct = $options['current_product'];

        $query = $qb->hydrate(false)->getQuery();
        $queryDefinition = $query->getQuery();

        if (null !== $currentProduct && isset($queryDefinition['sort']['normalizedData.is_associated'])) {
            $pipeline = $this->pipelineFromQuery($currentProduct, $queryDefinition, $associationTypeId);
            $collection = $query->getDocumentManager()->getDocumentCollection('Pim\Bundle\CatalogBundle\Model\Product');
            $results = $collection->aggregate($pipeline)->toArray();
        } else {
            $results = $query->execute();
        }

        $attributes = [];
        foreach ($config as $attributeConf) {
            $attributes[$attributeConf['id']] = $attributeConf;
        }

        $rows = [];
        $fieldsTransformer = new FieldsTransformer();
        $valuesTransformer = new ValuesTransformer();
        $familyTransformer = new FamilyTransformer();
        $complTransformer = new CompletenessTransformer();
        $groupsTransformer = new GroupsTransformer();

        foreach ($results as $result) {
            $result = $fieldsTransformer->transform($result, $locale);
            $result = $valuesTransformer->transform($result, $attributes, $locale, $scope);
            $result = $familyTransformer->transform($result, $locale);
            $result = $complTransformer->transform($result, $locale, $scope);
            $result = $groupsTransformer->transform($result, $locale, $groupId);
            $result['is_checked'] = $result['is_associated'];

            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }

    /**
     * @param ProductInterface $currentProduct
     * @param array            $queryDefinition
     * @param int              $associationTypeId
     *
     * @return array
     */
    protected function pipelineFromQuery(ProductInterface $currentProduct, array $queryDefinition, $associationTypeId)
    {
        $or = [];
        foreach ($currentProduct->getAssociations() as $association) {
            if ($associationTypeId != $association->getAssociationType()->getId()) {
                continue;
            }
            foreach ($association->getProducts() as $myProduct) {
                $or[] = ['$eq' => ['$_id', new \MongoId($myProduct->getId())]];
            }
        }

        $match = $queryDefinition['query'];
        $direction = $queryDefinition['sort']['normalizedData.is_associated'];
        $limit = $queryDefinition['limit'];
        $skip = $queryDefinition['skip'];

        $productReflection = new \ReflectionClass($currentProduct);
        $productFields = array_fill_keys(array_keys($productReflection->getDefaultProperties()), 1);

        $productFields['is_associated'] = [
            '$cond' => [
                ['$or' => $or],
                1,
                0
            ]
        ];

        $pipeline = [
            ['$match' => $match],
            ['$project' => $productFields],
            [
                '$sort' => [
                    'is_associated' => $direction
                ]
            ],
            ['$skip' => $skip],
            ['$limit' => $limit],
        ];

        return $pipeline;
    }
}
