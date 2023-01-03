<?php

declare(strict_types=1);

namespace Specification\AkeneoEnterprise\Category\Application\Storage\Save;

use Akeneo\Category\Application\Storage\Save\CategorySaverRegistry;
use Akeneo\Category\Application\Storage\Save\Saver\CategorySaver;
use Akeneo\Category\Domain\Model\Enrichment\Category;
use AkeneoEnterprise\Category\Api\Command\UserIntents\AddPermission;
use AkeneoEnterprise\Category\Api\Command\UserIntents\RemovePermission;
use AkeneoEnterprise\Category\Application\Storage\Save\Remover\CategoryPermissionRemover;
use AkeneoEnterprise\Category\Application\Storage\Save\Saver\CategoryPermissionSaver;
use PhpSpec\ObjectBehavior;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class PermissionSaverProcessorSpec extends ObjectBehavior
{
    function let(CategorySaverRegistry $categorySaverRegistry)
    {
        $this->beConstructedWith($categorySaverRegistry);
    }

    function it_uses_the_correct_savers_based_on_user_intent_list(
        CategorySaverRegistry $categorySaverRegistry,
        CategoryPermissionSaver $categoryPermissionSaver,
        CategoryPermissionRemover $categoryPermissionRemover,
        Category $category
    )
    {
        $addPermissionUserIntent = new AddPermission('view', [['id' => 1, 'label' => 'User group 1']]);
        $removePermissionUserIntent = new RemovePermission('view', [['id' => 1, 'label' => 'User group 1']]);

        $categorySaverRegistry->fromUserIntent($addPermissionUserIntent::class)->willReturn($categoryPermissionSaver);
        $categorySaverRegistry->fromUserIntent($removePermissionUserIntent::class)->willReturn($categoryPermissionRemover);

        $categoryPermissionSaver->save($category)->shouldBeCalled();
        $categoryPermissionRemover->save($category)->shouldBeCalled();

        $this->save($category, [$addPermissionUserIntent, $removePermissionUserIntent]);
    }

    function it_throws_an_exception_when_the_saver_class_was_not_added_into_the_savers_list(
        CategorySaverRegistry $categorySaverRegistry,
        Category $category,
        CategorySaver $unexpectedSaver,
        CategoryPermissionSaver $categoryPermissionSaver,
        CategoryPermissionRemover $categoryPermissionRemover,
    )
    {
        $addPermissionUserIntent = new AddPermission('view', [['id' => 1, 'label' => 'User group 1']]);
        $removePermissionUserIntent = new RemovePermission('view', [['id' => 1, 'label' => 'User group 1']]);
        $setUnexpectedUserIntent1 = new AddPermission('edit', [['id' => 1, 'label' => 'User group 1']]);
        $setUnexpectedUserIntent2 = new RemovePermission('edit', [['id' => 1, 'label' => 'User group 1']]);

        $categorySaverRegistry->fromUserIntent($addPermissionUserIntent::class)->willReturn($categoryPermissionSaver);
        $categorySaverRegistry->fromUserIntent($removePermissionUserIntent::class)->willReturn($categoryPermissionRemover);
        $categorySaverRegistry->fromUserIntent($setUnexpectedUserIntent1::class)->willReturn($unexpectedSaver);
        $categorySaverRegistry->fromUserIntent($setUnexpectedUserIntent2::class)->willReturn($unexpectedSaver);

        $categoryPermissionSaver->save($category)->shouldNotBeCalled();
        $categoryPermissionRemover->save($category)->shouldNotBeCalled();
        $unexpectedSaver->save($category)->shouldNotBeCalled();

        $this->shouldThrow(\LogicException::class)
            ->duringSave($category, [$addPermissionUserIntent, $removePermissionUserIntent]);
    }
}
