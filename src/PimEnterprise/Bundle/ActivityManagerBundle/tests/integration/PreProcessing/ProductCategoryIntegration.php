<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\tests\integration\PreProcessing;

use PimEnterprise\Bundle\ActivityManagerBundle\tests\integration\ActivityManagerTestCase;

class ProductCategoryIntegration extends ActivityManagerTestCase
{
    /**
     * Product : categoryless, tshirt-the-witcher-3
     * Channel: ecommerce
     * Locale: en_US
     */
    public function testTheLinkBetweenProductAndCategory()
    {
        $project = $this->createProject([
            'label'           => 'categoriesless-project',
            'locale'          => 'en_US',
            'owner'           => 'admin',
            'channel'         => 'ecommerce',
            'product_filters' => [
                [
                    'field'    => 'sku',
                    'operator' => 'IN',
                    'value'    => ['categoriesless', 'tshirt-the-witcher-3'],
                    'context'  => ['locale' => 'en_US', 'scope' => 'ecommerce'],
                ],
            ],
        ]);

        $productId = $this->get('pim_catalog.repository.product')
            ->findOneByIdentifier('tshirt-the-witcher-3')
            ->getId();

        $categoryId = $this->get('pim_catalog.repository.category')
            ->findOneByIdentifier('clothing')
            ->getId();

        $sql = <<<SQL
SELECT `product_category`.`product_id`, `product_category`.`category_id` FROM `pimee_activity_manager_product_category` AS `product_category`
INNER JOIN `pimee_activity_manager_project_product` AS `project_product`
	ON `product_category`.`product_id` = `project_product`.`product_id`
WHERE `project_id` = :project_id
SQL;

        $result = $this->getConnection()->fetchAll($sql, [
            'project_id' => $project->getId(),
        ]);

        $this->assertEquals(
            $result[0],
            [
                'product_id'  => $productId,
                'category_id' => $categoryId,
            ],
            'Invalid processed categories'
        );

        $this->assertEquals(
            count($result),
            1,
            'Invalid number of category'
        );
    }
}
