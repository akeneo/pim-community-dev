<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Asset\Integration\Persistence\Query;

use Akeneo\Asset\Bundle\Persistence\Query\Sql\IsDefaultAssetCategoryTreeOfAUser;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Samir Boulil <samir.boulil@akeneo.com>
 */
class IsDefaultAssetCategoryTreeOfAUserIntegration extends TestCase
{
    private const DEFAULT_ASSET_CATEGORY_TREE = 'asset_main_catalog';

    /** @var IsDefaultAssetCategoryTreeOfAUser  */
    private $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('pimee_product_asset.query.is_default_asset_category_tree_of_a_user');
    }

    public function testItTellsIfACategoryCodeIsADefaultAssetCategoryTreeOfAUser()
    {
        self::assertTrue($this->query->fetch(self::DEFAULT_ASSET_CATEGORY_TREE), 'The category is a default asset tree of a user');
        self::assertFalse($this->query->fetch('not_default_asset_category_tree_of_a_user'), 'The category is a default asset tree of a user');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
