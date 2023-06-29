<?php

namespace Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier;

use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SwitchMainIdentifierHandler
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function __invoke(SwitchMainIdentifierCommand $command): void
    {
        $this->switchMainIdentifier(
            $this->getFormerMainIdentifierCode(),
            $command->getNewMainIdentifierCode()
        );
    }

    private function getFormerMainIdentifierCode(): string
    {
        $sql = <<<SQL
SELECT code
FROM pim_catalog_attribute
WHERE main_identifier = 1
SQL;
        $result = $this->connection->fetchOne($sql);
        Assert::string($result, 'No main identifier found');

        return $result;
    }

    private function switchMainIdentifier(
        string $formerMainIdentifierCode,
        string $newMainIdentifierCode
    ): void {
        $sql = <<<SQL
UPDATE pim_catalog_attribute
SET
    main_identifier = IF(main_identifier, 0, 1),
    updated = NOW()
WHERE code IN (:formerMainIdentifier, :newMainIdentifier)
SQL;

        $this->connection->executeQuery($sql, [
            'formerMainIdentifierCode' => $formerMainIdentifierCode,
            'newMainIdentifierCode' => $newMainIdentifierCode,
        ]);
    }
}
