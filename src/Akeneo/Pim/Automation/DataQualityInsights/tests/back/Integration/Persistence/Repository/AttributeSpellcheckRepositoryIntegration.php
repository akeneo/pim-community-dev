<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeSpellcheckRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeSpellcheckRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class AttributeSpellcheckRepositoryIntegration extends TestCase
{
    /** @var Connection */
    private $dbConnection;

    /** @var AttributeSpellcheckRepositoryInterface */
    private $repository;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbConnection = $this->get('database_connection');
        $this->repository = $this->get(AttributeSpellcheckRepository::class);
    }

    public function test_it_saves_an_attribute_spellcheck()
    {
        $this->assertCountAttributeSpellchecks(0);

        $attributeSpellcheck = new AttributeSpellcheck(
            new AttributeCode('description'),
            new \DateTimeImmutable(),
            new SpellcheckResultByLocaleCollection()
        );

        $this->repository->save($attributeSpellcheck);
        $this->assertCountAttributeSpellchecks(1);

        $savedAttributeSpellcheck = $this->getAttributeSpellcheck('description');
        $this->assertAttributeSpellcheckEquals($attributeSpellcheck, $savedAttributeSpellcheck);

        $updatedAttributeSpellcheck = new AttributeSpellcheck(
            new AttributeCode('description'),
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove())
        );

        $this->repository->save($updatedAttributeSpellcheck);
        $this->assertCountAttributeSpellchecks(1);

        $savedAttributeSpellcheck = $this->getAttributeSpellcheck('description');
        $this->assertAttributeSpellcheckEquals($updatedAttributeSpellcheck, $savedAttributeSpellcheck);
    }

    public function test_it_deletes_spellchecks_of_unknown_attributes()
    {
        $this->createAttribute('description');
        $this->createAttributeSpellcheck('description');
        $this->createAttributeSpellcheck('unknown_attribute');
        $this->assertCountAttributeSpellchecks(2);

        $this->repository->deleteUnknownAttributes();
        $this->assertCountAttributeSpellchecks(1);
    }

    private function assertCountAttributeSpellchecks(int $expectedCount): void
    {
        $stmt = $this->dbConnection->executeQuery('SELECT COUNT(*) FROM pimee_dqi_attribute_spellcheck;');

        $this->assertSame($expectedCount, intval($stmt->fetchOne()), sprintf('There should be %d attribute spellchecks', $expectedCount));
    }

    private function assertAttributeSpellcheckEquals(AttributeSpellcheck $expectedAttributeSpellcheck, array $attributeSpellcheck): void
    {
        $this->assertEquals($expectedAttributeSpellcheck->getAttributeCode(), $attributeSpellcheck['attribute_code']);
        $this->assertSame($expectedAttributeSpellcheck->getEvaluatedAt()->format(Clock::TIME_FORMAT), $attributeSpellcheck['evaluated_at']);
        $this->assertSame($expectedAttributeSpellcheck->getResult()->isToImprove(), isset($attributeSpellcheck['to_improve']) ? (bool) $attributeSpellcheck['to_improve'] : null);
        $this->assertEquals($expectedAttributeSpellcheck->getResult()->toArrayBool(), json_decode($attributeSpellcheck['result'], true));
    }

    private function getAttributeSpellcheck(string $attributeCode): array
    {
        $stmt = $this->dbConnection->executeQuery(
            "SELECT * FROM pimee_dqi_attribute_spellcheck WHERE attribute_code = '$attributeCode';"
        );

        $attributeSpellcheck = $stmt->fetchAssociative();
        $this->assertIsArray($attributeSpellcheck, sprintf('No attribute spellcheck for "%s"', $attributeCode));

        return $attributeSpellcheck;
    }

    private function createAttribute(string $code): void
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createAttributeSpellcheck(string $attributeCode): void
    {
        $this->get(AttributeSpellcheckRepository::class)->save(new AttributeSpellcheck(
            new AttributeCode($attributeCode),
            new \DateTimeImmutable(),
            new SpellcheckResultByLocaleCollection()
        ));
    }
}
