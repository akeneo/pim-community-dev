<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Common\Helper\FixturesLoader;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class SqlAttributeExistsTest extends SqlIntegrationTestCase
{
    /** @var AttributeExistsInterface */
    private $attributeExists;

    /** @var array */
    private $fixtures;

    public function setUp(): void
    {
        parent::setUp();

        $this->attributeExists = $this->get('akeneo_referenceentity.infrastructure.persistence.query.attribute_exists');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_returns_true_if_the_attribute_exists_for_the_given_identifier()
    {
        $identifier = $this->fixtures['attributes']['name']->getIdentifier();
        $isExisting = $this->attributeExists->withIdentifier($identifier);
        Assert::assertTrue($isExisting);
    }

    /**
     * @test
     */
    public function it_returns_false_if_the_attribute_does_not_exist_for_the_given_identifier()
    {
        $isExisting = $this->attributeExists->withIdentifier(AttributeIdentifier::create('designer', 'name', 'none'));
        Assert::assertFalse($isExisting);
    }

    /**
     * @test
     */
    public function it_says_if_the_attribute_exists_for_the_given_reference_entity_identifier_and_order()
    {
        $isExistingAtOrder2 = $this->attributeExists->withReferenceEntityIdentifierAndOrder(ReferenceEntityIdentifier::fromString('designer'), AttributeOrder::fromInteger(2));
        $isExistingAtOrder3 = $this->attributeExists->withReferenceEntityIdentifierAndOrder(ReferenceEntityIdentifier::fromString('designer'), AttributeOrder::fromInteger(3));

        Assert::assertTrue($isExistingAtOrder2);
        Assert::assertFalse($isExistingAtOrder3);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        /** @var FixturesLoader $fixturesLoader */
        $fixturesLoader = $this->get('akeneoreference_entity.tests.helper.fixtures_loader');
        $this->fixtures = $fixturesLoader
            ->referenceEntity('designer')
            ->withAttributes(['name'])
            ->load();
    }
}
