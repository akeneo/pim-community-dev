<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Query;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\IsAttributeEditable;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use AkeneoTestEnterprise\Pim\Permission\FixturesLoader\AttributeGroupFixturesLoader;
use PHPUnit\Framework\Assert;

class IsAttributeEditableIntegration extends TestCase
{
    private IsAttributeEditable $isAttributeEditable;
    private AttributeGroupFixturesLoader $attributeGroupFixturesLoader;

    /** @test */
    public function it_returns_false_when_the_attribute_does_not_exist(): void
    {
        Assert::assertFalse($this->isAttributeEditable->forCode('unknown', $this->getUserId('julia')));
    }

    /** @test */
    public function it_returns_false_when_the_user_cannot_edit_the_attribute(): void
    {
        Assert::assertFalse($this->isAttributeEditable->forCode('a_text', $this->getUserId('mary')));
    }

    /** @test */
    public function it_returns_true_when_the_user_can_edit_the_attribute(): void
    {
        Assert::assertTrue($this->isAttributeEditable->forCode('a_text', $this->getUserId('julia')));
        Assert::assertTrue($this->isAttributeEditable->forCode('a_text_area', $this->getUserId('mary')));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->attributeGroupFixturesLoader = $this->get('akeneo_integration_tests.loader.attribute_group');

        $this->createUser('julia', ['ROLE_CATALOG_MANAGER'], ['Manager']);
        $this->createUser('mary', ['ROLE_USER'], ['Redactor']);

        $attributeGroupA = $this->attributeGroupFixturesLoader->createAttributeGroup(['code' => 'attributeGroupA']);
        $attributeGroupC = $this->attributeGroupFixturesLoader->createAttributeGroup(['code' => 'attributeGroupC']);

        $attributeGroupAccessManager = $this->get('pimee_security.manager.attribute_group_access');
        $attributeGroupAccessManager->setAccess(
            $attributeGroupA,
            $this->getUserGroups(['Manager', 'Redactor']), // view user group
            $this->getUserGroups(['Manager', 'Redactor']) // edit user group
        );
        $attributeGroupAccessManager->setAccess(
            $attributeGroupC,
            $this->getUserGroups(['Manager']), // view user group
            $this->getUserGroups(['Manager']) // edit user group
        );

        $this->createAttribute('a_text', 'attributeGroupA');
        $this->createAttribute('a_text_area', 'attributeGroupA');

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('a_text');
        $this->get('pim_catalog.updater.attribute')->update($attribute, [ 'group' => 'attributeGroupC']);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertCount(0, $violations);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $this->isAttributeEditable = $this->get('Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\IsAttributeEditable');
    }

    private function getUserId(string $userName): int
    {
        return (int) $this->get('database_connection')->executeQuery(
            'SELECT id from oro_user WHERE username = :username',
            ['username' => $userName]
        )->fetchOne();
    }

    /**
     * @param string[] $groupNames
     *
     * @return array
     */
    private function getUserGroups(array $groupNames): array
    {
        return array_filter($this->get('pim_user.repository.group')->findAll(), function ($group) use ($groupNames) {
            return in_array($group->getName(), $groupNames);
        });
    }

    /**
     * @param string[] $stringRoles
     * @param string[] $groupNames
     */
    protected function createUser(string $username, array $stringRoles, array $groupNames): UserInterface
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername($username);
        $user->setFirstName($username);
        $user->setLastName($username);
        $user->setPassword('password');
        $user->setEmail($username . '@example.com');

        $groups = $this->get('pim_user.repository.group')->findAll();
        foreach ($groups as $group) {
            if (\in_array($group->getName(), $groupNames)) {
                $user->addGroup($group);
            }
        }

        $roles = $this->get('pim_user.repository.role')->findAll();
        foreach ($roles as $role) {
            if (\in_array($role->getRole(), $stringRoles)) {
                $user->addRole($role);
            }
        }

        $violations = $this->get('validator')->validate($user);
        Assert::assertSame(0, $violations->count(), (string) $violations);
        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }

    private function createAttribute(string $code, string $attributeGroup): void
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => $attributeGroup,
        ], true);
        $violations = $this->get('validator')->validate($attribute);
        Assert::assertSame(0, $violations->count(), (string) $violations);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }
}
