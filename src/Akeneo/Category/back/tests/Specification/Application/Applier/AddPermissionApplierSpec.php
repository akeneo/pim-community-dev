<?php

namespace Specification\AkeneoEnterprise\Category\Application\Applier;

use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use AkeneoEnterprise\Category\Api\Command\UserIntents\AddPermission;
use AkeneoEnterprise\Category\Application\Applier\AddPermissionApplier;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
                'view' => [
                    ['id' => 1, 'label' => 'User group 1'],
                ],
                'edit' => [
                    ['id' => 1, 'label' => 'User group 1'],
                ],
                'own' => [
                    ['id' => 1, 'label' => 'User group 1'],
                ]
            ])
        );

        $addViewPermission = new AddPermission('view', [['id' => 2, 'label' => 'User group 2']]);
        $addEditPermission = new AddPermission('edit', [['id' => 2, 'label' => 'User group 2']]);

        $this->apply($addViewPermission, $category);
        $this->apply($addEditPermission, $category);

        Assert::assertEquals([
            ['id' => 1, 'label' => 'User group 1'],
            ['id' => 2, 'label' => 'User group 2'],
        ], $category->getPermissions()->getViewUserGroups());
        Assert::assertEquals([
            ['id' => 1, 'label' => 'User group 1'],
            ['id' => 2, 'label' => 'User group 2'],
        ], $category->getPermissions()->getEditUserGroups());
    }
}
