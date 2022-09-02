<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Symfony\Command;

use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class CleanCaseSensitiveAttributeOptionSpellcheckCommandIntegration extends DataQualityInsightsTestCase
{
    private const COMMAND_NAME = 'pimee:data-quality-insights:clean-case-sensitive-attribute-option-spellcheck';

    public function test_it_cleans_case_sensitive_attribute_options_spellcheck(): void
    {
        $this->removePreviousCleaning();

        $this->givenAnAttributeWithOptions('color', ['red', 'blue', 'Green']);
        $this->givenAnAttributeWithOptions('secondary_color', ['white', 'Green']);

        $this->givenCaseSensitiveDifferenceOnAttributeOption('color', 'green');
        $this->givenCaseSensitiveDifferenceOnAttributeOption('secondary_color', 'White');

        $this->assertCountAttributeOptionsSpellcheck(5);
        $this->launchCleaning();
        $this->assertCountAttributeOptionsSpellcheck(3);
        $this->assertAttributeOptionsSpellcheckHasBeenCleaned('color', 'green');
        $this->assertAttributeOptionsSpellcheckHasBeenCleaned('secondary_color', 'White');
    }

    private function launchCleaning(): void
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);

        $command = $application->find(self::COMMAND_NAME);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()], ['capture_stderr_separately' => true]);

        self::assertEquals(0, $commandTester->getStatusCode(), $commandTester->getErrorOutput());
    }

    private function removePreviousCleaning(): void
    {
        $this->get('database_connection')->executeQuery(
            'DELETE FROM pim_one_time_task WHERE code = :taskCode;',
            ['taskCode' => self::COMMAND_NAME]
        );
    }

    private function givenAnAttributeWithOptions(string $attributeCode, array $optionCodes): void
    {
        $this->createSimpleSelectAttributeWithOptions($attributeCode, $optionCodes);

        $query = <<<SQL
INSERT IGNORE INTO pimee_dqi_attribute_option_spellcheck (attribute_code, attribute_option_code, evaluated_at)
VALUES (:attributeCode, :attributeOptionCode, NOW())
SQL;

        foreach ($optionCodes as $optionCode) {
            $this->get('database_connection')->executeQuery($query, [
                'attributeCode' => $attributeCode,
                'attributeOptionCode' => $optionCode,
            ]);
        }
    }

    private function givenCaseSensitiveDifferenceOnAttributeOption(string $attributeCode, string $optionCode): void
    {
        $query = <<<SQL
UPDATE pimee_dqi_attribute_option_spellcheck
SET attribute_option_code = :attributeOptionCode
WHERE attribute_code = :attributeCode AND attribute_option_code = :attributeOptionCode;
SQL;
        $this->get('database_connection')->executeQuery($query, [
            'attributeCode' => $attributeCode,
            'attributeOptionCode' => $optionCode,
        ]);
    }

    private function assertCountAttributeOptionsSpellcheck(int $expectedCount): void
    {
        $count = $this->get('database_connection')->executeQuery(
            'SELECT COUNT(*) FROM pimee_dqi_attribute_option_spellcheck;'
        )->fetchOne();

        $this->assertSame($expectedCount, \intval($count), sprintf('There should be %d attribute options spellcheck', $expectedCount));
    }

    private function assertAttributeOptionsSpellcheckHasBeenCleaned(string $attributeCode, string $optionCode): void
    {
        $query = <<<SQL
SELECT 1 FROM pimee_dqi_attribute_option_spellcheck
WHERE attribute_code = :attributeCode AND attribute_option_code = :attributeOptionCode;
SQL;

        $spellcheckExist = $this->get('database_connection')->executeQuery($query, [
            'attributeCode' => $attributeCode,
            'attributeOptionCode' => $optionCode,
        ])->fetchOne();

        $this->assertFalse($spellcheckExist, sprintf('The option "%s" of attribute "%s" should have been cleaned', $attributeCode, $optionCode));
    }
}
