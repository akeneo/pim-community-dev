<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Storage\Sql;

use Akeneo\Test\Integration\TestCase;

/**
 * +----------+--------------------------------------------------------------------------+
 * |          |                             Categories                                   |
 * +  Roles   +--------------------------------------------------------------------------+
 * |          |     master    |   categoryA   |  categoryA1 / categoryA2 |   categoryB   |
 * +----------+--------------------------------------------------------------------------+
 * | Redactor | View,Edit,Own |   View,Edit   |            View          |       -       |
 * | Manager  | View,Edit,Own | View,Edit,Own |       View,Edit,Own      | View,Edit,Own |
 * +----------+--------------------------------------------------------------------------+
 */
class IsUserOwnerOnAllCategoriesQueryIntegration extends TestCase
{
    public function test_it_returns_false_if_the_user_is_not_owner_on_all_the_given_categories()
    {
        $query = $this->get('pimee_workflow.query.is_user_owner_on_all_categories');

        $productBuilder = $this->get('pim_catalog.builder.product');
        $productA = $productBuilder->createProduct('product_a', 'familyA');
        $productB = $productBuilder->createProduct('product_b', 'familyA');

        $productUpdater = $this->get('pim_catalog.updater.product');
        $productUpdater->update($productA, ['categories' => ['master']]);
        $productUpdater->update($productB, ['categories' => ['categoryA', 'categoryB']]);

        $this->get('pim_catalog.saver.product')->saveAll([$productA, $productB]);

        $this->assertFalse($query->execute('mary', ['master', 'categoryA']));
    }

    public function test_it_returns_true_if_the_user_is_owner_on_all_the_given_categories()
    {
        $query = $this->get('pimee_workflow.query.is_user_owner_on_all_categories');

        $productBuilder = $this->get('pim_catalog.builder.product');
        $productA = $productBuilder->createProduct('product_a', 'familyA');
        $productB = $productBuilder->createProduct('product_b', 'familyA');

        $productUpdater = $this->get('pim_catalog.updater.product');
        $productUpdater->update($productA, ['categories' => ['master']]);
        $productUpdater->update($productB, ['categories' => ['categoryA', 'categoryB']]);

        $this->get('pim_catalog.saver.product')->saveAll([$productA, $productB]);

        $this->assertTrue($query->execute('julia', ['master', 'categoryA', 'categoryB']));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
