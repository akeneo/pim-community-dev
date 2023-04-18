<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\CouldNotFindIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\UnableToFetchIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ReadModelIdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReadModelIdentifierGeneratorRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlReadModelIdentifierGeneratorRepository implements ReadModelIdentifierGeneratorRepository
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function get(string $identifierGeneratorCode): ReadModelIdentifierGenerator
    {
        $sql = <<<SQL
SELECT
    BIN_TO_UUID(uuid) AS uuid,
    pim_catalog_identifier_generator.code,
    conditions,
    structure,
    labels,
    options,
    pim_catalog_attribute.code AS target
FROM pim_catalog_identifier_generator
INNER JOIN pim_catalog_attribute ON pim_catalog_identifier_generator.target_id=pim_catalog_attribute.id
WHERE pim_catalog_identifier_generator.code=:code
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam('code', $identifierGeneratorCode, \PDO::PARAM_STR);

        try {
            $result = $stmt->executeQuery()->fetchAssociative();
            if (!$result) {
                throw new CouldNotFindIdentifierGeneratorException($identifierGeneratorCode);
            }

            return $this->fromDatabaseToModel($result);
        } catch (DriverException | \InvalidArgumentException) {
            throw new UnableToFetchIdentifierGeneratorException(\sprintf('Cannot fetch the identifier generator "%s"', $identifierGeneratorCode));
        }
    }

    private function fromDatabaseToModel(array $result): ReadModelIdentifierGenerator
    {
        $options = \json_decode($result['options'], true);

        return new ReadModelIdentifierGenerator(
            IdentifierGeneratorId::fromString($result['uuid']),
            IdentifierGeneratorCode::fromString($result['code']),
            Conditions::fromNormalized(\json_decode($result['conditions'], true)),
            Structure::fromNormalized(\json_decode($result['structure'], true)),
            LabelCollection::fromNormalized(\json_decode($result['labels'], true)),
            Target::fromString($result['target']),
            Delimiter::fromString($options['delimiter']),
            TextTransformation::fromString($options['text_transformation'])
        );
    }

}
