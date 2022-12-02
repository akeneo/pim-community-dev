<?php

namespace Specification\Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\AddPermission;
use Akeneo\Category\Application\Applier\AddPermissionApplier;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

class AddPermissionApplierSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AddPermissionApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    public function it_applies_add_permission_user_intent(): void
    {
        $category = new Category(
            id: new CategoryId(1),
            code: new Code('my_category'),
            templateUuid: null,
            labels: LabelCollection::fromArray([]),
            parentId: null,
            permissions: PermissionCollection::fromArray([
                'view' => [1, 2, 5],
                'edit' => [1, 2, 5],
                'own' => [1, 2, 5]
            ])
        );

        $addViewPermission = new AddPermission('view', [3]);
        $addEditPermission = new AddPermission('edit', [3]);

        $this->apply($addViewPermission, $category);
        $this->apply($addEditPermission, $category);
        Assert::assertEquals([1, 2, 5, 3], $category->getPermissions()->getViewUserGroups());
        Assert::assertEquals([1, 2, 5, 3], $category->getPermissions()->getEditUserGroups());
    }
}
