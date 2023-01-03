<?php

namespace Specification\AkeneoEnterprise\Category\Application\Applier;

use Akeneo\Category\Application\Applier\UserIntentApplier;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\PermissionCollection;
use AkeneoEnterprise\Category\Api\Command\UserIntents\RemovePermission;
use AkeneoEnterprise\Category\Application\Applier\RemovePermissionApplier;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
                'view' => [
                    ['id' => 1, 'label' => 'User group 1'],
                    ['id' => 2, 'label' => 'User group 2'],
                ],
                'edit' => [
                    ['id' => 1, 'label' => 'User group 1'],
                    ['id' => 2, 'label' => 'User group 2'],
                ],
                'own' => [
                    ['id' => 1, 'label' => 'User group 1'],
                    ['id' => 2, 'label' => 'User group 2'],
                ],
            ])
        );

        $removeOwnPermission = new RemovePermission('own', [['id' => 2, 'label' => 'User group 2']]);

        $this->apply($removeOwnPermission, $category);

        Assert::assertEquals([
            ['id' => 1, 'label' => 'User group 1'],
        ], $category->getPermissions()->getOwnUserGroups());
    }
}
