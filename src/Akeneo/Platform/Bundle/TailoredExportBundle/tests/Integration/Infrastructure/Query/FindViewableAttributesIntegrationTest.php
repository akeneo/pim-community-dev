<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Test\Integration\Infrastructure\Query;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Platform\TailoredExport\Domain\Query\Attribute\FindViewableAttributesInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\Attribute\ViewableAttributesResult;
use Akeneo\Platform\TailoredExport\Test\Integration\IntegrationTestCase;
use Akeneo\Test\Integration\Configuration;

class FindViewableAttributesIntegrationTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->loadData();
    }

    /**
     * @test
     */
    public function it_batches_results()
    {
        $this->logAs('admin');
        $viewableAttributesResult = $this->getQuery()->execute('en_US', 3, null, 0, null);
        $this->assertEquals(3, $viewableAttributesResult->getOffset());
        $this->assertResultContainAttributeCodes([
            'text_viewable_by_all',
            'image_viewable_by_all',
            'multi_select_viewable_by_admin',
        ], $viewableAttributesResult);

        $viewableAttributesResult = $this->getQuery()->execute('en_US', 3, null, 3, null);
        $this->assertEquals(6, $viewableAttributesResult->getOffset());
        $this->assertResultContainAttributeCodes([
            'file_viewable_by_admin',
            'simple_select_viewable_by_admin_and_manager',
            'date_viewable_by_admin_and_manager',
        ], $viewableAttributesResult);

        $viewableAttributesResult = $this->getQuery()->execute('en_US', 3, null, 6, null);
        $this->assertEquals(7, $viewableAttributesResult->getOffset());
        $this->assertResultContainAttributeCodes([
            'sku',
        ], $viewableAttributesResult);

        $viewableAttributesResult = $this->getQuery()->execute('en_US', 3, null, 7, null);
        $this->assertEquals(7, $viewableAttributesResult->getOffset());
        $this->assertResultContainAttributeCodes([], $viewableAttributesResult);
    }

    /**
     * @test
     */
    public function it_only_returns_viewable_attributes_for_the_current_user()
    {
        $this->logAs('admin');
        $viewableAttributesResult = $this->getQuery()->execute('en_US', 10, null, 0, null);
        $this->assertEquals(7, $viewableAttributesResult->getOffset());
        $this->assertResultContainAttributeCodes([
            'text_viewable_by_all',
            'image_viewable_by_all',
            'multi_select_viewable_by_admin',
            'file_viewable_by_admin',
            'simple_select_viewable_by_admin_and_manager',
            'date_viewable_by_admin_and_manager',
            'sku',
        ], $viewableAttributesResult);

        $this->logAs('mary');
        $viewableAttributesResult = $this->getQuery()->execute('en_US', 10, null, 0, null);
        $this->assertEquals(7, $viewableAttributesResult->getOffset());
        $this->assertResultContainAttributeCodes([
            'text_viewable_by_all',
            'image_viewable_by_all',
            'simple_select_viewable_by_admin_and_manager',
            'date_viewable_by_admin_and_manager',
            'sku',
        ], $viewableAttributesResult);
    }

    /**
     * @test
     */
    public function it_filter_not_viewable_attributes_and_return_the_real_offset()
    {
        $this->logAs('mary');
        $viewableAttributesResult = $this->getQuery()->execute('en_US', 3, null, 0, null);
        $this->assertEquals(5, $viewableAttributesResult->getOffset());
        $this->assertResultContainAttributeCodes([
            'text_viewable_by_all',
            'image_viewable_by_all',
            'simple_select_viewable_by_admin_and_manager',
        ], $viewableAttributesResult);
    }

    /**
     * @test
     */
    public function it_filters_attributes_by_types()
    {
        $this->logAs('admin');
        $viewableAttributesResult = $this->getQuery()->execute('en_US', 3, [AttributeTypes::DATE, AttributeTypes::TEXT], 0, null);
        $this->assertEquals(2, $viewableAttributesResult->getOffset());
        $this->assertResultContainAttributeCodes([
            'text_viewable_by_all',
            'date_viewable_by_admin_and_manager',
        ], $viewableAttributesResult);
    }

    /**
     * @test
     */
    public function it_only_returns_attribute_corresponding_to_search()
    {
        $this->logAs('admin');
        $viewableAttributesResult = $this->getQuery()->execute('en_US', 3, null, 0, 'by_admin_and_manager');
        $this->assertEquals(2, $viewableAttributesResult->getOffset());
        $this->assertResultContainAttributeCodes([
            'simple_select_viewable_by_admin_and_manager',
            'date_viewable_by_admin_and_manager',
        ], $viewableAttributesResult);
    }

    private function assertResultContainAttributeCodes(
        array $expectedAttributeCodes,
        ViewableAttributesResult $viewableAttributesResult
    ) {
        $actualAttributeCodes = array_map(static fn ($attribute) => $attribute->getCode(), $viewableAttributesResult->getAttributes());
        $this->assertEquals($expectedAttributeCodes, $actualAttributeCodes);
    }

    private function loadData()
    {
        $this->createUser('admin', 'IT support');
        $this->createUser('mary', 'Manager');
        $this->createAttributeGroup(['code' => 'viewable_by_all', 'sort_order' => 2], ['All']);
        $this->createAttributeGroup(['code' => 'viewable_by_admin', 'sort_order' => 3], ['IT support']);
        $this->createAttributeGroup(['code' => 'viewable_by_admin_and_manager', 'sort_order' => 4], ['IT support', 'Manager']);

        $this->createAttribute([
            'code' => 'text_viewable_by_all',
            'type' => AttributeTypes::TEXT,
            'localizable' => false,
            'scopable' => true,
            'group' => 'viewable_by_all',
            'sort_order' => 1,
        ]);

        $this->createAttribute([
            'code' => 'image_viewable_by_all',
            'type' => AttributeTypes::IMAGE,
            'localizable' => true,
            'scopable' => false,
            'group' => 'viewable_by_all',
            'sort_order' => 2,
        ]);

        $this->createAttribute([
            'code' => 'multi_select_viewable_by_admin',
            'type' => AttributeTypes::OPTION_MULTI_SELECT,
            'localizable' => false,
            'scopable' => true,
            'group' => 'viewable_by_admin',
            'sort_order' => 1,
        ]);

        $this->createAttribute([
            'code' => 'file_viewable_by_admin',
            'type' => AttributeTypes::FILE,
            'localizable' => true,
            'scopable' => false,
            'group' => 'viewable_by_admin',
            'sort_order' => 2,
        ]);

        $this->createAttribute([
            'code' => 'simple_select_viewable_by_admin_and_manager',
            'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
            'localizable' => false,
            'scopable' => true,
            'group' => 'viewable_by_admin_and_manager',
            'sort_order' => 1,
        ]);

        $this->createAttribute([
            'code' => 'date_viewable_by_admin_and_manager',
            'type' => AttributeTypes::DATE,
            'localizable' => true,
            'scopable' => false,
            'group' => 'viewable_by_admin_and_manager',
            'sort_order' => 2,
        ]);
    }

    private function createUser(string $username, string $groupName): void
    {
        $user = $this->get('pim_user.factory.user')->create();
        $this->get('pim_user.updater.user')->update($user, [
            'username' => $username,
            'groups' => [$groupName],
            'email' => sprintf('%s@example.com', $username),
            'password' => 'fake',
            'last_name' => $username,
            'first_name' => $username
        ]);

        $this->assertEntityIsValid($user);

        $this->get('pim_user.saver.user')->save($user);
    }

    private function createAttributeGroup(array $normalizedAttributeGroup, array $userGroupNameWithViewPermission): void
    {
        $attributeGroup = $this->get('pim_catalog.factory.attribute_group')->create();
        $this->get('pim_catalog.updater.attribute_group')->update($attributeGroup, $normalizedAttributeGroup);
        $this->assertEntityIsValid($attributeGroup);
        $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);

        $userGroupWithViewPermission = $this->get('pim_user.repository.group')->findBy(['name' => $userGroupNameWithViewPermission]);
        $this->get('pimee_security.manager.attribute_group_access')->setAccess(
            $attributeGroup,
            $userGroupWithViewPermission,
            []
        );
    }

    private function createAttribute(array $normalizedAttribute)
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $normalizedAttribute);
        $this->assertEntityIsValid($attribute);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function getQuery(): FindViewableAttributesInterface
    {
        return $this->get(
            'Akeneo\Platform\TailoredExport\Domain\Query\Attribute\FindViewableAttributesInterface'
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
