<?php

namespace Specification\Akeneo\Category\Application\Applier;

use Akeneo\Category\Api\Command\UserIntents\RemovePermission;
use Akeneo\Category\Application\Applier\RemovePermissionApplier;
use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

class RemovePermissionApplierSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(RemovePermissionApplier::class);
        $this->shouldImplement(UserIntentApplier::class);
    }

    public function it_applies_remove_permission_user_intent(): void
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

        $removeOwnPermission = new RemovePermission('own', [2]);

        $this->apply($removeOwnPermission, $category);
        Assert::assertEquals([1, 5], $category->getPermissions()->getOwnUserGroups());
    }
}
