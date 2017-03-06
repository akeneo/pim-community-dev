<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\tests\integration\PreProcessing;

use PimEnterprise\Bundle\TeamworkAssistantBundle\tests\integration\TeamworkAssistantTestCase;

class ProductCategoryIntegration extends TeamworkAssistantTestCase
{
    /**
     * Product : categoryless, tshirt-the-witcher-3
     * Channel: ecommerce
     * Locale: en_US
     */
    public function testTheLinkBetweenProductAndCategory()
    {
        $project = $this->createProject('categoriesless-project', 'admin', 'en_US', 'ecommerce', [
            [
                'field'    => 'sku',
                'operator' => 'IN',
                'value'    => ['categoriesless', 'tshirt-the-witcher-3'],
            ],
        ]);

        $productId = $this->get('pim_catalog.repository.product')
            ->findOneByIdentifier('tshirt-the-witcher-3')
            ->getId();

        $categoryId = $this->get('pim_catalog.repository.category')
            ->findOneByIdentifier('clothing')
            ->getId();

        $sql = <<<SQL
SELECT `product_category`.`product_id`, `product_category`.`category_id` 
FROM `@pim_catalog.entity.product#categories@` AS `product_category`
INNER JOIN `@pimee_teamwork_assistant.project_product@` AS `project_product`
	ON `product_category`.`product_id` = `project_product`.`product_id`
WHERE `project_id` = :project_id
SQL;

        $sql = $this->get('pimee_teamwork_assistant.table_name_mapper')->createQuery($sql);

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
