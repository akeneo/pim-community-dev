<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateSaver;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTreeTemplateSaver;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Pim\Permission\Bundle\Saver\UserGroupCategoryPermissionsSaver;
use Akeneo\Test\Integration\Configuration;
use Akeneo\UserManagement\Component\Model\Group;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnterpriseGetCategorySqlIntegration extends CategoryTestCase
{
    private Category $category;
    private Group $userGroup1;
    private Group $userGroup2;
    private Group $userGroup3;
    private TemplateUuid $templateUuid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = $this->createOrUpdateCategory(
            code: "socks",
            labels: ['en_US' => 'socks', 'fr_FR' => 'Chaussettes'],
        );

        $template = $this->givenTemplateWithAttributes(
            templateUuidRaw: '02274dac-e99a-4e1d-8f9b-794d4c3ba330',
            categoryId: $this->category->getId()
        );
        $this->templateUuid = $template->getUuid();
        $this->get(CategoryTemplateSaver::class)->insert($template);
        $this->get(CategoryTreeTemplateSaver::class)->insert($template);
        $this->get(CategoryTemplateAttributeSaver::class)->insert(
            $template->getUuid(),
            $template->getAttributeCollection(),
        );

        $this->userGroup1 = $this->createUserGroup('group1');
        $this->userGroup2 = $this->createUserGroup('group2');
        $this->userGroup3 = $this->createUserGroup('group3');

        $this->get(UserGroupCategoryPermissionsSaver::class)->save($this->userGroup1->getName(), [
            'own' => ['all' => false, 'identifiers' => ['socks']],
            'edit' => ['all' => false, 'identifiers' => ['socks']],
            'view' => ['all' => false, 'identifiers' => ['socks']],
        ]);

        $this->get(UserGroupCategoryPermissionsSaver::class)->save($this->userGroup2->getName(), [
            'own' => ['all' => false, 'identifiers' => []],
            'edit' => ['all' => false, 'identifiers' => ['socks']],
            'view' => ['all' => false, 'identifiers' => ['socks']],
        ]);

        $this->get(UserGroupCategoryPermissionsSaver::class)->save($this->userGroup3->getName(), [
            'own' => ['all' => false, 'identifiers' => []],
            'edit' => ['all' => false, 'identifiers' => []],
            'view' => ['all' => false, 'identifiers' => ['socks']],
        ]);
    }

    public function testGetCategoryByCode(): void
    {
        $category = $this->get(GetCategoryInterface::class)->byCode((string) $this->category->getCode());

        $this->assertInstanceOf(Category::class, $category);
        $this->assertPermissions($category);
        $this->assertTemplate($category);
    }

    public function testGetCategoryById(): void
    {
        $category = $this->get(GetCategoryInterface::class)->byId($this->category->getId()->getValue());

        $this->assertInstanceOf(Category::class, $category);
        $this->assertPermissions($category);
        $this->assertTemplate($category);
    }

    private function assertPermissions(Category $category): void
    {
        $this->assertContains($this->userGroup1->getId(), $category->getPermissions()->getViewUserGroups());
        $this->assertContains($this->userGroup1->getId(), $category->getPermissions()->getEditUserGroups());
        $this->assertContains($this->userGroup1->getId(), $category->getPermissions()->getOwnUserGroups());

        $this->assertContains($this->userGroup2->getId(), $category->getPermissions()->getViewUserGroups());
        $this->assertContains($this->userGroup2->getId(), $category->getPermissions()->getEditUserGroups());
        $this->assertNotContains($this->userGroup2->getId(), $category->getPermissions()->getOwnUserGroups());

        $this->assertContains($this->userGroup3->getId(), $category->getPermissions()->getViewUserGroups());
        $this->assertNotContains($this->userGroup3->getId(), $category->getPermissions()->getEditUserGroups());
        $this->assertNotContains($this->userGroup3->getId(), $category->getPermissions()->getOwnUserGroups());
    }

    private function assertTemplate(Category $category): void
    {
        Assert::assertSame($this->templateUuid->getValue(), $category->getTemplateUuid()->getValue());
    }

    private function createUserGroup(string $name): Group
    {
        $userGroup = new Group();
        $userGroup->setName($name);

        $violations = $this->get('validator')->validate($userGroup);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_user.saver.group')->save($userGroup);

        return $userGroup;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
