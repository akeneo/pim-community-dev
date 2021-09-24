<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Bundle\Saver;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetAllRootCategoriesCodes;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetCategoriesAccessesWithHighestLevel;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetRootCategoryReferenceFromCode;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use PhpSpec\ObjectBehavior;

class UserGroupCategoryPermissionsSaverSpec extends ObjectBehavior
{
    function let(
        CategoryAccessManager $categoryAccessManager,
        GroupRepository $groupRepository,
        SaverInterface $groupSaver,
        GetRootCategoryReferenceFromCode $getRootCategoryReferenceFromCode,
        GetAllRootCategoriesCodes $getAllRootCategoriesCodes,
        GetCategoriesAccessesWithHighestLevel $getCategoriesAccessesWithHighestLevel,
        GroupInterface $group,
        Category $categoryA,
        Category $categoryB,
        Category $categoryC
    ) {
        $group->getId()->willReturn(42);

        $categoryA->getId()->willReturn(1);
        $categoryA->getCode()->willReturn('category_a');
        $categoryB->getId()->willReturn(2);
        $categoryB->getCode()->willReturn('category_b');
        $categoryC->getId()->willReturn(3);
        $categoryC->getCode()->willReturn('category_c');

        $getRootCategoryReferenceFromCode->execute('category_a')->willReturn($categoryA);
        $getRootCategoryReferenceFromCode->execute('category_b')->willReturn($categoryB);
        $getRootCategoryReferenceFromCode->execute('category_c')->willReturn($categoryC);

        $getAllRootCategoriesCodes->execute()->willReturn([
            'category_a',
            'category_b',
            'category_c',
        ]);

        $groupRepository->findOneByIdentifier('Redactor')->willReturn($group);

        $getCategoriesAccessesWithHighestLevel->execute(42)->willReturn([]);

        $this->beConstructedWith(
            $categoryAccessManager,
            $groupRepository,
            $groupSaver,
            $getRootCategoryReferenceFromCode,
            $getAllRootCategoriesCodes,
            $getCategoriesAccessesWithHighestLevel,
        );
    }

    /**
     * FROM nothing
     * TO {"own":{"all":true,"identifiers":[]},"edit":{"all":true,"identifiers":[]},"view":{"all":true,"identifiers":[]}}
     */
    public function it_GrantPermissionsOnExistingCategoriesWhenTheAllByDefaultOptionIsEnabled(
        CategoryAccessManager $categoryAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        Category $categoryA,
        Category $categoryB,
        Category $categoryC
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $group->setDefaultPermission('category_own', true)->shouldBeCalled();
        $group->setDefaultPermission('category_edit', true)->shouldBeCalled();
        $group->setDefaultPermission('category_view', true)->shouldBeCalled();
        $groupSaver->save($group)->shouldBeCalled();

        $categoryAccessManager->grantAccess($categoryA, $group, Attributes::OWN_PRODUCTS)->shouldBeCalled();
        $categoryAccessManager->grantAccess($categoryB, $group, Attributes::OWN_PRODUCTS)->shouldBeCalled();
        $categoryAccessManager->grantAccess($categoryC, $group, Attributes::OWN_PRODUCTS)->shouldBeCalled();

        $this->save('Redactor', [
            'own' => [
                'all' => true,
                'identifiers' => [],
            ],
            'edit' => [
                'all' => true,
                'identifiers' => [],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ]);
    }


    /**
     * FROM {"own":{"all":true,"identifiers":[]},"edit":{"all":true,"identifiers":[]},"view":{"all":true,"identifiers":[]}}
     * TO {"own":{"all":false,"identifiers":[]},"edit":{"all":false,"identifiers":[]},"view":{"all":true,"identifiers":[]}}
     */
    public function it_CorrectsGrantedPermissionsOnExistingCategoriesWhenTheDefaultOptionIsReducedToViewOnly(
        CategoryAccessManager $categoryAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        Category $categoryA,
        Category $categoryB,
        Category $categoryC
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $group->setDefaultPermission('category_own', false)->shouldBeCalled();
        $group->setDefaultPermission('category_edit', false)->shouldBeCalled();
        $group->setDefaultPermission('category_view', true)->shouldBeCalled();
        $groupSaver->save($group)->shouldBeCalled();

        $categoryAccessManager->grantAccess($categoryA, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();
        $categoryAccessManager->grantAccess($categoryB, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();
        $categoryAccessManager->grantAccess($categoryC, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();

        $this->save('Redactor', [
            'own' => [
                'all' => false,
                'identifiers' => [],
            ],
            'edit' => [
                'all' => false,
                'identifiers' => [],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ]);
    }

    /**
     * FROM nothing
     * TO {"own":{"all":false,"identifiers":["category_a"]},"edit":{"all":false,"identifiers":["category_a"]},"view":{"all":false,"identifiers":["category_a"]}}
     */
    public function it_GrantPermissionsOnExistingCategoriesWhenIdentifiersAreSelected(
        CategoryAccessManager $categoryAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        Category $categoryA
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $groupSaver->save($group)->shouldNotBeCalled();

        $categoryAccessManager->grantAccess($categoryA, $group, Attributes::OWN_PRODUCTS)->shouldBeCalled();

        $this->save('Redactor', [
            'own' => [
                'all' => false,
                'identifiers' => [
                    'category_a',
                ],
            ],
            'edit' => [
                'all' => false,
                'identifiers' => [
                    'category_a',
                ],
            ],
            'view' => [
                'all' => false,
                'identifiers' => [
                    'category_a',
                ],
            ],
        ]);
    }

    /**
     * FROM nothing
     * TO {"own":{"all":false,"identifiers":["category_a"]},"edit":{"all":false,"identifiers":["category_a"]},"view":{"all":true,"identifiers":[]}}
     */
    public function it_GrantPermissionsOnExistingCategoriesWhenIdentifiersAndTheDefaultOptionAreMixed(
        CategoryAccessManager $categoryAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        Category $categoryA,
        Category $categoryB,
        Category $categoryC
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $group->setDefaultPermission('category_own', false)->shouldBeCalled();
        $group->setDefaultPermission('category_edit', false)->shouldBeCalled();
        $group->setDefaultPermission('category_view', true)->shouldBeCalled();
        $groupSaver->save($group)->shouldBeCalled();

        $categoryAccessManager->grantAccess($categoryA, $group, Attributes::OWN_PRODUCTS)->shouldBeCalled();
        $categoryAccessManager->grantAccess($categoryB, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();
        $categoryAccessManager->grantAccess($categoryC, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();

        $this->save('Redactor', [
            'own' => [
                'all' => false,
                'identifiers' => [
                    'category_a',
                ],
            ],
            'edit' => [
                'all' => false,
                'identifiers' => [
                    'category_a',
                ],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ]);
    }

    /**
     * FROM nothing
     * TO {"own":{"all":false,"identifiers":[]},"edit":{"all":false,"identifiers":["category_a"]},"view":{"all":false,"identifiers":["category_a", "category_b"]}}
     */
    public function it_GrantPermissionsOnCategoriesWhenIdentifiersAreOnDifferentLevels(
        CategoryAccessManager $categoryAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        Category $categoryA,
        Category $categoryB
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $groupSaver->save($group)->shouldNotBeCalled();

        $categoryAccessManager->grantAccess($categoryA, $group, Attributes::EDIT_ITEMS)->shouldBeCalled();
        $categoryAccessManager->grantAccess($categoryB, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();

        $this->save('Redactor', [
            'own' => [
                'all' => false,
                'identifiers' => [],
            ],
            'edit' => [
                'all' => false,
                'identifiers' => [
                    'category_a',
                ],
            ],
            'view' => [
                'all' => false,
                'identifiers' => [
                    'category_a',
                    'category_b',
                ],
            ],
        ]);
    }

    /**
     * FROM {"own":{"all":false,"identifiers":["category_a"]},"edit":{"all":false,"identifiers":["category_a"]},"view":{"all":false,"identifiers":["category_a"]}}
     * TO {"own":{"all":false,"identifiers":["category_a"]},"edit":{"all":false,"identifiers":["category_a"]},"view":{"all":false,"identifiers":["category_a"]}}
     */
    public function it_doesNothingWhenIdentifiersWereAlreadySelected(
        CategoryAccessManager $categoryAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        Category $categoryA,
        GetCategoriesAccessesWithHighestLevel $getCategoriesAccessesWithHighestLevel
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $groupSaver->save($group)->shouldNotBeCalled();
        $getCategoriesAccessesWithHighestLevel->execute(42)->willReturn(['category_a' => Attributes::OWN_PRODUCTS]);

        $categoryAccessManager->grantAccess($categoryA, $group, Attributes::OWN_PRODUCTS)->shouldNotBeCalled();

        $this->save('Redactor', [
            'own' => [
                'all' => false,
                'identifiers' => [
                    'category_a',
                ],
            ],
            'edit' => [
                'all' => false,
                'identifiers' => [
                    'category_a',
                ],
            ],
            'view' => [
                'all' => false,
                'identifiers' => [
                    'category_a',
                ],
            ],
        ]);
    }

    /**
     * FROM {"own":{"all":false,"identifiers":["category_a"]},"edit":{"all":false,"identifiers":["category_a"]},"view":{"all":false,"identifiers":["category_a"]}}
     * TO {"own":{"all":false,"identifiers":[]},"edit":{"all":false,"identifiers":[]},"view":{"all":false,"identifiers":[]}}
     */
    public function it_removeAccessWhenIdentifiersAreRemoved(
        CategoryAccessManager $categoryAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        Category $categoryA,
        GetCategoriesAccessesWithHighestLevel $getCategoriesAccessesWithHighestLevel
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $groupSaver->save($group)->shouldNotBeCalled();
        $getCategoriesAccessesWithHighestLevel->execute(42)->willReturn(['category_a' => Attributes::OWN_PRODUCTS]);

        $categoryAccessManager->revokeGroupAccess($categoryA, $group)->shouldBeCalled();

        $this->save('Redactor', [
            'own' => [
                'all' => false,
                'identifiers' => [],
            ],
            'edit' => [
                'all' => false,
                'identifiers' => [],
            ],
            'view' => [
                'all' => false,
                'identifiers' => [],
            ],
        ]);
    }

    /**
     * FROM {"own":{"all":false,"identifiers":["category_a"]},"edit":{"all":false,"identifiers":["category_a"]},"view":{"all":false,"identifiers":["category_a"]}}
     * TO {"own":{"all":false,"identifiers":[]},"edit":{"all":false,"identifiers":[]},"view":{"all":false,"identifiers":["category_a"]}}
     */
    public function it_updatesPermissionsWhenIdentifiersAreRemoved(
        CategoryAccessManager $categoryAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        Category $categoryA,
        GetCategoriesAccessesWithHighestLevel $getCategoriesAccessesWithHighestLevel
    ) {
        $group->getDefaultPermissions()->willReturn([]);
        $groupSaver->save($group)->shouldNotBeCalled();
        $getCategoriesAccessesWithHighestLevel->execute(42)->willReturn(['category_a' => Attributes::OWN_PRODUCTS]);

        $categoryAccessManager->grantAccess($categoryA, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();

        $this->save('Redactor', [
            'own' => [
                'all' => false,
                'identifiers' => [],
            ],
            'edit' => [
                'all' => false,
                'identifiers' => [],
            ],
            'view' => [
                'all' => false,
                'identifiers' => [
                    'category_a',
                ],
            ],
        ]);
    }

    /**
     * FROM {"own":{"all":false,"identifiers":["category_a", "category_b"]},"edit":{"all":true,"identifiers":[]},"view":{"all":true,"identifiers":[]}}
     * TO {"own":{"all":false,"identifiers":["category_a"]},"edit":{"all":false,"identifiers":["category_a", "category_b"]},"view":{"all":true,"identifiers":[]}}
     */
    public function it_updatesPermissionsWhenSwitchingFromAllByDefaultToSpecificIdentifiers(
        CategoryAccessManager $categoryAccessManager,
        SaverInterface $groupSaver,
        GroupInterface $group,
        GetCategoriesAccessesWithHighestLevel $getCategoriesAccessesWithHighestLevel,
        Category $categoryA,
        Category $categoryB,
        Category $categoryC
    ) {
        $group->getDefaultPermissions()->willReturn([
            'category_edit' => true,
            'category_view' => true,
        ]);
        $group->setDefaultPermission('category_own', false)->shouldBeCalled();
        $group->setDefaultPermission('category_edit', false)->shouldBeCalled();
        $group->setDefaultPermission('category_view', true)->shouldBeCalled();
        $groupSaver->save($group)->shouldBeCalled();

        $getCategoriesAccessesWithHighestLevel->execute(42)->willReturn([
            'category_a' => Attributes::OWN_PRODUCTS,
            'category_b' => Attributes::OWN_PRODUCTS,
            'category_c' => Attributes::EDIT_ITEMS,
        ]);

        $categoryAccessManager->grantAccess($categoryB, $group, Attributes::EDIT_ITEMS)->shouldBeCalled();
        $categoryAccessManager->grantAccess($categoryC, $group, Attributes::VIEW_ITEMS)->shouldBeCalled();

        $this->save('Redactor', [
            'own' => [
                'all' => false,
                'identifiers' => [
                    'category_a',
                ],
            ],
            'edit' => [
                'all' => false,
                'identifiers' => [
                    'category_a',
                    'category_b',
                ],
            ],
            'view' => [
                'all' => true,
                'identifiers' => [],
            ],
        ]);
    }
}
