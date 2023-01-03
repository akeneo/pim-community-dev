<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Integration\ServiceApi\Category;

use Akeneo\Category\ServiceApi\CategoryQueryInterface;
use Akeneo\Pim\Permission\Bundle\Persistence\Sql\Category\GetCategoryProductPermissionsSql;
use Akeneo\Pim\Permission\Bundle\ServiceApi\Category\GetCategoryProductPermissionsByCategoryId;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class GetCategoryProductPermissionsByCategoryIdIntegration extends TestCase
{
    public function testItInvokeGetCategoryProductPermissions(): void
    {
        $this->createCategory([
            'code' => 'socks',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ]
        ]);
        $category = $this->get(CategoryQueryInterface::class)->byCode('socks');

        $mockGetCategoryPermission = $this->createMock(GetCategoryProductPermissionsSql::class);
        $mockGetCategoryPermission
            ->expects($this->once())
            ->method("__invoke")
            ->with($category->getId());

        $getCategoryProductPermissionsByCategoryId = new GetCategoryProductPermissionsByCategoryId($mockGetCategoryPermission);

        $permissions = ($getCategoryProductPermissionsByCategoryId)($category->getId());

        Assert::assertNotNull($permissions);
    }

    public function testItReturnPermissions(): void
    {
        $this->createCategory([
            'code' => 'socks',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ]
        ]);
        $category = $this->get(CategoryQueryInterface::class)->byCode('socks');
        $expectedPermissions = [
            "view" => [ ["id" => 1, "label" => "IT Support"], ["id" => 3, "label" => "Redactor"] ],
            "edit" => [ ["id" => 1, "label" => "IT Support"], ["id" => 3, "label" => "Redactor"] ],
            "own" => [ ["id" => 1, "label" => "IT Support"], ["id" => 3, "label" => "Redactor"] ],
        ];

        $mockGetCategoryPermission = $this->createMock(GetCategoryProductPermissionsSql::class);
        $mockGetCategoryPermission
            ->method("__invoke")
            ->willReturn($expectedPermissions);

        $getCategoryProductPermissionsByCategoryId = new GetCategoryProductPermissionsByCategoryId($mockGetCategoryPermission);

        $permissions = ($getCategoryProductPermissionsByCategoryId)($category->getId());

        Assert::assertEquals($expectedPermissions, $permissions);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
