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

namespace Akeneo\Category\tests\Integration\Infrastructure\Permission;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Pim\Permission\Bundle\ServiceApi\Category\GetCategoryProductPermissionsByCategoryIdInterface;
use AkeneoEnterprise\Category\Infrastructure\Permission\FindCategoryProductPermissions;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class FindCategoryProductPermissionsIntegration extends CategoryTestCase
{
    private SecurityFacade $securityFacade;
    private GetCategoryProductPermissionsByCategoryIdInterface $getCategoryProductPermissionsByCategoryId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->securityFacade = $this->get('oro_security.security_facade');
        $this->getCategoryProductPermissionsByCategoryId = $this->get(GetCategoryProductPermissionsByCategoryIdInterface::class);
    }

    public function testExecuteAndReturnCategoryWithPermissions(): void
    {
        $category = $this->insertBaseCategory(new Code('my_category'));
        $expectedPermissions = [
            "view" => [ ["id" => 1, "label" => "IT Support"], ["id" => 3, "label" => "Redactor"] ],
            "edit" => [ ["id" => 1, "label" => "IT Support"], ["id" => 3, "label" => "Redactor"] ],
            "own" => [ ["id" => 1, "label" => "IT Support"], ["id" => 3, "label" => "Redactor"] ],
        ];

        $mockGetCategoryProductPermissionsByCategoryId = $this->createMock($this->getCategoryProductPermissionsByCategoryId::class);
        $mockGetCategoryProductPermissionsByCategoryId
            ->expects($this->once())
            ->method("__invoke")
            ->willReturn($expectedPermissions);

        $findCategoryProductPermissions = new FindCategoryProductPermissions(
            $this->securityFacade,
            /* @phpstan-ignore-next-line */
            $mockGetCategoryProductPermissionsByCategoryId
        );

        $categoryWithPermissions = $findCategoryProductPermissions->execute($category);

        $this->assertSame($expectedPermissions, $categoryWithPermissions->getPermissions()->normalize());
    }

    public function testIsSupportedAdditionalProperties(): void
    {
        $mockSecurityFacade = $this->createMock($this->securityFacade::class);
        $mockSecurityFacade->method("isGranted")->willReturn(true);

        $findCategoryProductPermissions = new FindCategoryProductPermissions(
            $mockSecurityFacade,
            /* @phpstan-ignore-next-line */
            $this->getCategoryProductPermissionsByCategoryId
        );

        $isSupported = $findCategoryProductPermissions->isSupportedAdditionalProperties();

        $this->assertTrue($isSupported);
    }

    public function testIsNotSupportedAdditionalProperties(): void
    {
        $mockSecurityFacade = $this->createMock($this->securityFacade::class);
        $mockSecurityFacade->method("isGranted")->willReturn(false);

        $findCategoryProductPermissions = new FindCategoryProductPermissions(
            $mockSecurityFacade,
            /* @phpstan-ignore-next-line */
            $this->getCategoryProductPermissionsByCategoryId
        );

        $isSupported = $findCategoryProductPermissions->isSupportedAdditionalProperties();

        $this->assertFalse($isSupported);
    }
}
