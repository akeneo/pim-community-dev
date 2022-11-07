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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeOptionSpellcheckRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeOptionSpellcheckRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\AttributeGrid\TestCase;
use Doctrine\DBAL\Connection;

class AttributeOptionSpellcheckRepositoryIntegration extends TestCase
{
    /** @var Connection */
    private $dbConnection;

    /** @var AttributeOptionSpellcheckRepositoryInterface */
    private $repository;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbConnection = $this->get('database_connection');
        $this->repository = $this->get(AttributeOptionSpellcheckRepository::class);
    }

    public function test_it_saves_an_attribute_option_spellcheck()
    {
        $this->assertCountAttributeOptionSpellchecks(0);

        $attributeOptionSpellcheck = $this->createAttributeOptionSpellcheck('color', 'red');
        $this->assertCountAttributeOptionSpellchecks(1);

        $savedAttributeSpellcheck = $this->getAttributeOptionSpellcheck('color', 'red');
        $this->assertAttributeOptionSpellcheckEquals($attributeOptionSpellcheck, $savedAttributeSpellcheck);

        $updatedSpellcheckResult = (new SpellcheckResultByLocaleCollection())
            ->add(new LocaleCode('en_US'), SpellCheckResult::good())
            ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove());
        $updatedAttributeOptionSpellcheck = $this->createAttributeOptionSpellcheck('color', 'red', $updatedSpellcheckResult);

        $this->repository->save($updatedAttributeOptionSpellcheck);
        $this->assertCountAttributeOptionSpellchecks(1);

        $savedAttributeSpellcheck = $this->getAttributeOptionSpellcheck('color', 'red');
        $this->assertAttributeOptionSpellcheckEquals($updatedAttributeOptionSpellcheck, $savedAttributeSpellcheck);
    }

    public function test_it_deletes_spellchecks_of_unknown_attribute_options()
    {
        $this->givenASimpleSelectAttribute('color');
        $this->givenAnAttributeOption('color', 'red');
        $this->givenAnAttributeOption('color', 'blue');
        $this->givenASimpleSelectAttribute('material');

        $this->createAttributeOptionSpellcheck('color', 'red');
        $this->createAttributeOptionSpellcheck('color', 'blue');
        $this->createAttributeOptionSpellcheck('color', 'green');
        $this->createAttributeOptionSpellcheck('material', 'wood');
        $this->createAttributeOptionSpellcheck('foo', 'bar');

        $this->assertCountAttributeOptionSpellchecks(5);

        $this->repository->deleteUnknownAttributeOptions('color');
        $this->assertCountAttributeOptionSpellchecks(2);

        $this->assertNotEmpty($this->getAttributeOptionSpellcheck('color', 'red'));
        $this->assertNotEmpty($this->getAttributeOptionSpellcheck('color', 'blue'));
    }

    public function test_it_deletes_spellchecks_of_unknown_attribute_option()
    {
        $this->givenASimpleSelectAttribute('color');
        $this->givenAnAttributeOption('color', 'red');
        $attributeOptionColorBlue = $this->givenAnAttributeOption('color', 'blue');

        $this->createAttributeOptionSpellcheck('color', 'red');
        $this->createAttributeOptionSpellcheck('color', 'blue');
        $this->createAttributeOptionSpellcheck('color', 'green');

        $this->assertCountAttributeOptionSpellchecks(3);

        $this->repository->deleteUnknownAttributeOption('color');
        $this->assertCountAttributeOptionSpellchecks(2);

        $this->assertNotEmpty($this->getAttributeOptionSpellcheck('color', 'red'));
        $this->assertNotEmpty($this->getAttributeOptionSpellcheck('color', 'blue'));
    }

    private function getAttributeOptionSpellcheck(string $attributeCode, string $optionCode): array
    {
        $query = <<<SQL
SELECT * FROM pimee_dqi_attribute_option_spellcheck
WHERE attribute_code = :attributeCode AND attribute_option_code = :optionCode
SQL;

        $stmt = $this->dbConnection->executeQuery($query, [
            'attributeCode' => $attributeCode,
            'optionCode' => $optionCode,
        ]);

        $attributeOptionSpellcheck = $stmt->fetchAssociative();
        $this->assertIsArray($attributeOptionSpellcheck, sprintf('No attribute option spellcheck found for "%s"', $attributeCode));

        return $attributeOptionSpellcheck;
    }

    private function assertCountAttributeOptionSpellchecks(int $expectedCount): void
    {
        $stmt = $this->dbConnection->executeQuery('SELECT COUNT(*) FROM pimee_dqi_attribute_option_spellcheck;');

        $this->assertSame($expectedCount, intval($stmt->fetchOne()), sprintf('There should be %d attribute option spellchecks', $expectedCount));
    }

    private function assertAttributeOptionSpellcheckEquals(AttributeOptionSpellcheck $expectedAttributeSpellcheck, array $attributeOptionSpellcheck): void
    {
        $this->assertEquals($expectedAttributeSpellcheck->getAttributeCode(), $attributeOptionSpellcheck['attribute_code']);
        $this->assertEquals($expectedAttributeSpellcheck->getAttributeOptionCode(), $attributeOptionSpellcheck['attribute_option_code']);
        $this->assertSame($expectedAttributeSpellcheck->getEvaluatedAt()->format(Clock::TIME_FORMAT), $attributeOptionSpellcheck['evaluated_at']);
        $this->assertSame($expectedAttributeSpellcheck->getResult()->isToImprove(), isset($attributeOptionSpellcheck['to_improve']) ? (bool) $attributeOptionSpellcheck['to_improve'] : null);
        $this->assertEquals($expectedAttributeSpellcheck->getResult()->toArrayBool(), json_decode($attributeOptionSpellcheck['result'], true));
    }

    private function createAttributeOptionSpellcheck(string $attributeCode, string $optionCode, ?SpellcheckResultByLocaleCollection $result = null): AttributeOptionSpellcheck
    {
        $attributeOptionSpellcheck = new AttributeOptionSpellcheck(
            new AttributeOptionCode(new AttributeCode($attributeCode), $optionCode),
            new \DateTimeImmutable(),
            $result ?? new SpellcheckResultByLocaleCollection()
        );

        $this->repository->save($attributeOptionSpellcheck);

        return $attributeOptionSpellcheck;
    }

    private function givenASimpleSelectAttribute(string $attributeCode): void
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $attributeCode,
            'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
            'group' => 'other',
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function givenAnAttributeOption(string $attributeCode, string $optionCode): AttributeOptionInterface
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
            'code' => $optionCode,
            'attribute' => $attributeCode,
        ]);
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);

        return $attributeOption;
    }
}
