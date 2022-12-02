<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Specification\Akeneo\Category\Domain\UserIntent\Factory;

use Akeneo\Category\Api\Command\UserIntents\AddPermission;
use Akeneo\Category\Api\Command\UserIntents\RemovePermission;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class PermissionUserIntentFactorySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith();
    }

    function it_manage_only_expected_field_names()
    {
        $this->getSupportedFieldNames()->shouldReturn(['permissions']);
    }

    function it_creates_a_list_of_add_and_remove_permissions_user_intents_based_on_permissions_list(): void
    {
        $this->create(
            'permissions',
            [
                'view' => [1, 2, 5, 3],
                'edit' => [1, 2, 5, 3],
                'own' => [1, 5]
            ]
        )->shouldBeLike([
            new AddPermission('view', [3 => 3]),
            new AddPermission('edit', [3 => 3]),
            new RemovePermission('own', [1 => 2]),
        ]);
    }

    function it_throws_an_exception_when_data_has_wrong_format(): void
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['permissions', null]);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['permissions', 'data']);

        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('create', ['permissions', true]);
    }
}
