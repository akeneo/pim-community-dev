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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CleanCaseSensitiveAttributeOptionSpellcheckCommand extends Command
{
    protected static $defaultName = 'pimee:data-quality-insights:clean-case-sensitive-attribute-option-spellcheck';

    public function __construct(
        private Connection $dbConnection
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Clean attribute option codes that have case difference between pim_catalog_attribute_option and pimee_dqi_attribute_option_spellcheck');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (false === $this->cleaningCanBeStarted()) {
            $output->writeln('This cleaning has already been performed or is in progress.', OutputInterface::VERBOSITY_VERBOSE);
            return Command::SUCCESS;
        }
        $this->cleaningStarted();

        $totalCleaning = 0;
        foreach ($this->getAttributeCodesWithOptions() as $attributeCode) {
            $attributeOptionCodesToClean = $this->getAttributeOptionCodesToClean($attributeCode);
            if (! empty($attributeOptionCodesToClean)) {
                $totalCleaning += count($attributeOptionCodesToClean);
                $this->cleanAttributeOptionSpellchecks($attributeCode, $attributeOptionCodesToClean);
            }
        }

        $output->writeln(sprintf('%s attribute options spellcheck have been cleaned', $totalCleaning > 0 ? $totalCleaning : 'No'));
        $this->cleaningDone();

        return Command::SUCCESS;
    }

    private function getAttributeCodesWithOptions(): array
    {
        $query = <<<SQL
SELECT DISTINCT attribute.code
FROM pim_catalog_attribute AS attribute
INNER JOIN pim_catalog_attribute_option AS attribute_option ON attribute.id = attribute_option.attribute_id;
SQL;

        $rows = $this->dbConnection->executeQuery($query)->fetchAllAssociative();

        return array_map(fn ($row) => $row['code'], $rows);
    }

    private function getAttributeOptionCodesToClean(string $attributeCode): array
    {
        $query = <<<SQL
SELECT attribute_option.code AS attribute_option_code, spellcheck.attribute_option_code AS spellcheck_option_code
FROM pim_catalog_attribute_option AS attribute_option
INNER JOIN pim_catalog_attribute AS attribute ON attribute.id = attribute_option.attribute_id
INNER JOIN pimee_dqi_attribute_option_spellcheck AS spellcheck
ON spellcheck.attribute_code = attribute.code AND spellcheck.attribute_option_code = attribute_option.code
WHERE attribute.code = :attributeCode;
SQL;

        $stmt = $this->dbConnection->executeQuery($query, ['attributeCode' => $attributeCode]);

        $optionCodes = [];
        $spellcheckOptionCodes = [];
        while ($row = $stmt->fetchAssociative()) {
            $optionCodes[] = $row['attribute_option_code'];
            $spellcheckOptionCodes[] = $row['spellcheck_option_code'];
        }

        // The array_diff function is case-sensitive
        return array_diff($optionCodes, $spellcheckOptionCodes);
    }

    private function cleanAttributeOptionSpellchecks(string $attributeCode, array $attributeOptionCodesToClean): void
    {
        $query = <<<SQL
DELETE FROM pimee_dqi_attribute_option_spellcheck
WHERE attribute_code = :attributeCode
AND attribute_option_code IN (:attributeOptionCodes);
SQL;

        $this->dbConnection->executeQuery(
            $query,
            [
                'attributeCode' => $attributeCode,
                'attributeOptionCodes' => $attributeOptionCodesToClean,
            ],
            [
                'attributeOptionCodes' => Connection::PARAM_STR_ARRAY,
            ]
        );
    }

    private function cleaningStarted(): void
    {
        $query = <<<SQL
INSERT IGNORE INTO pim_one_time_task (code, status, start_time) VALUES
(:code, 'started', NOW());
SQL;

        $this->dbConnection->executeQuery($query, ['code' => self::$defaultName]);
    }


    private function cleaningDone(): void
    {
        $query = <<<SQL
UPDATE pim_one_time_task
SET status = 'done', end_time = NOW()
WHERE code = :code;
SQL;

        $this->dbConnection->executeQuery($query, ['code' => self::$defaultName]);
    }

    private function cleaningCanBeStarted(): bool
    {
        $query = <<<SQL
SELECT 1 FROM pim_one_time_task WHERE code = :code
SQL;

        return !(bool)$this->dbConnection->executeQuery($query, ['code' => self::$defaultName])->fetchOne();
    }
}
