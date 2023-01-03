<?php

declare(strict_types=1);

namespace Specification\AkeneoEnterprise\Category\Domain\UserIntent\Factory;

use Akeneo\Pim\Permission\Bundle\ServiceApi\Category\GetCategoryProductPermissionsByCategoryIdInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoEnterprise\Category\Api\Command\UserIntents\AddPermission;
use AkeneoEnterprise\Category\Api\Command\UserIntents\RemovePermission;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PermissionUserIntentFactorySpec extends ObjectBehavior
{
    public function let(GetCategoryProductPermissionsByCategoryIdInterface $getCategoryProductPermissionsByCategoryId): void
    {
        $this->beConstructedWith($getCategoryProductPermissionsByCategoryId);
    }

    function it_manage_only_expected_field_names()
    {
        $this->getSupportedFieldNames()->shouldReturn(['permissions']);
    }

    function it_creates_a_list_of_add_and_remove_permissions_user_intents_based_on_permissions_list($getCategoryProductPermissionsByCategoryId): void
    {
        $getCategoryProductPermissionsByCategoryId->__invoke(1)->willReturn([
            'view' => [
                ['id' => 1, 'label' => 'User group 1'],
            ],
            'edit' => [
                ['id' => 1, 'label' => 'User group 1'],
            ],
            'own' => [
                ['id' => 1, 'label' => 'User group 1'],
                ['id' => 2, 'label' => 'User group 2'],
            ],
        ])->shouldBeCalled();

        $this->create(
            'permissions',
            1,
            [
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
                ],
            ]
        )->shouldBeLike([
            new AddPermission('view', [['id' => 2, 'label' => 'User group 2']]),
            new AddPermission('edit', [['id' => 2, 'label' => 'User group 2']]),
            new RemovePermission('own', [['id' => 2, 'label' => 'User group 2']]),
        ]);
    }

    function it_throws_an_exception_when_data_has_wrong_format(): void
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['permissions', 1, null]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['permissions', 1, 'data']);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['permissions', 1, true]);
    }
}
