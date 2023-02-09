<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Builder;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\Query\GetCategoryInterface;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Category\Domain\ValueObject\Version\CategoryVersion;
use Akeneo\Category\Infrastructure\Builder\CategoryVersionBuilder;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CategoryVersionBuilderIntegration extends CategoryTestCase
{
    public function testCreateACategoryVersion(): void
    {
        $givenParent = new Category(
            id: new CategoryId(1),
            code: new Code('master'),
            templateUuid: null,
        );
        $getCategoryMock = $this->createMock(GetCategoryInterface::class);
        $getCategoryMock->method('byId')->willReturn($givenParent);
        $builder = new CategoryVersionBuilder($getCategoryMock);

        $updated = new \DateTimeImmutable();
        $givenCategory = new Category(
            id: new CategoryId(2),
            code: new Code('category_test'),
            templateUuid: TemplateUuid::fromUuid(Uuid::uuid4()),
            labels: LabelCollection::fromArray(['en_US' => 'test category', 'fr_FR' => 'catégorie de test']),
            parentId: $givenParent->getId(),
            parentCode: $givenParent->getCode(),
            rootId: $givenParent->getId(),
            updated: $updated,
            attributes: null,
            permissions: null,
            position: null,
        );

        $categoryVersion = $builder->create($givenCategory);

        $expectedCategoryVersion = CategoryVersion::fromBuilder(
            resourceId: '2',
            snapshot: [
                'code' => 'category_test',
                'parent' => 'master',
                'updated' => $updated->format('c'),
                'label-en_US' => 'test category',
                'label-fr_FR' => 'catégorie de test'
            ]
        );

        $this->assertVersion($expectedCategoryVersion, $categoryVersion);
    }

    public function testCreateACategoryVersionWithoutParent(): void
    {
        $getCategoryMock = $this->createMock(GetCategoryInterface::class);
        $getCategoryMock->expects($this->never())->method('byId');
        $builder = new CategoryVersionBuilder($getCategoryMock);

        $updated = new \DateTimeImmutable();
        $givenCategory = new Category(
            id: new CategoryId(2),
            code: new Code('category_test'),
            templateUuid: TemplateUuid::fromUuid(Uuid::uuid4()),
            labels: LabelCollection::fromArray(['en_US' => 'test category', 'fr_FR' => 'catégorie de test']),
            updated: $updated
        );

        $categoryVersion = $builder->create($givenCategory);

        $expectedCategoryVersion = CategoryVersion::fromBuilder(
            resourceId: '2',
            /** @phpstan-ignore-next-line  */
            snapshot: [
                'code' => 'category_test',
                'parent' => null,
                'updated' => $updated->format('c'),
                'label-en_US' => 'test category',
                'label-fr_FR' => 'catégorie de test'
            ]
        );

        $this->assertVersion($expectedCategoryVersion, $categoryVersion);
    }

    public function testCreateACategoryVersionWithPermissions(): void
    {
        $getCategoryMock = $this->createMock(GetCategoryInterface::class);
        $getCategoryMock->expects($this->never())->method('byId');
        $builder = new CategoryVersionBuilder($getCategoryMock);

        $updated = new \DateTimeImmutable();
        $givenCategory = new Category(
            id: new CategoryId(2),
            code: new Code('category_test'),
            templateUuid: TemplateUuid::fromUuid(Uuid::uuid4()),
            labels: LabelCollection::fromArray(['en_US' => 'test category', 'fr_FR' => 'catégorie de test']),
            updated: $updated,
            permissions: PermissionCollection::fromArray([
                'view'=> [['id' => 1, 'label' => 'Manager'], ['id' => 1, 'label' => 'Redactor']],
                'edit'=> [['id' => 1, 'label' => 'All']],
                'own'=> [['id' => 1, 'label' => 'All']],
            ]),
        );

        $categoryVersion = $builder->create($givenCategory);


        $expectedCategoryVersion = CategoryVersion::fromBuilder(
            resourceId: '2',
            snapshot: [
                'code' => 'category_test',
                'parent' => '',
                'updated' => $updated->format('c'),
                'label-en_US' => 'test category',
                'label-fr_FR' => 'catégorie de test',
                'view_permission' => 'Manager,Redactor',
                'edit_permission' => 'All',
                'own_permission' => 'All',
            ]
        );

        $this->assertVersion($expectedCategoryVersion, $categoryVersion);
    }

    private function assertVersion(CategoryVersion $expectedCategoryVersion, CategoryVersion $categoryVersion): void
    {
        $this->assertEquals($expectedCategoryVersion->getResourceId(), $categoryVersion->getResourceId());
        $this->assertEquals($expectedCategoryVersion->getSnapshot()['code'], $categoryVersion->getSnapshot()['code']);
        $this->assertEquals($expectedCategoryVersion->getSnapshot()['parent'], $categoryVersion->getSnapshot()['parent']);
        /** @phpstan-ignore-next-line */
        $this->assertEquals($expectedCategoryVersion->getSnapshot()['label-en_US'], $categoryVersion->getSnapshot()['label-en_US']);
        /** @phpstan-ignore-next-line */
        $this->assertEquals($expectedCategoryVersion->getSnapshot()['label-fr_FR'], $categoryVersion->getSnapshot()['label-fr_FR']);
        $this->assertArrayHasKey('updated', $categoryVersion->getSnapshot());
        $this->assertNotEmpty($categoryVersion->getSnapshot()['updated']);
    }
}
