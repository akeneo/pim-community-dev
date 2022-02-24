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

use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\IsAttributeEditable;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class IsAttributeEditableIntegration extends TestCase
{
    private IsAttributeEditable $isAttributeEditable;

    // or should we throw an exception ?
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
        Assert::assertTrue($this->isAttributeEditable->forCode('a_simple_select', $this->getUserId('mary')));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

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
}
