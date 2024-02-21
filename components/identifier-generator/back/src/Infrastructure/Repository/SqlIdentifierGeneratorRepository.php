<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\CouldNotFindIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\UnableToDeleteIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\UnableToFetchIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\UnableToSaveIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\UnableToUpdateIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlIdentifierGeneratorRepository implements IdentifierGeneratorRepository
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function save(IdentifierGenerator $identifierGenerator): void
    {
        $parameters = [
            'uuid' => $identifierGenerator->id()->asString(),
            'code' => $identifierGenerator->code()->asString(),
            'target' => $identifierGenerator->target()->asString(),
            'delimiter' => $identifierGenerator->delimiter()->asString(),
            'labels' => \json_encode($identifierGenerator->labelCollection()->normalize()),
            'conditions' => \json_encode($identifierGenerator->conditions()->normalize()),
            'structure' => \json_encode($identifierGenerator->structure()->normalize()),
            'text_transformation' => $identifierGenerator->textTransformation()->normalize(),
        ];

        $query = <<<SQL
INSERT INTO pim_catalog_identifier_generator (uuid, code, target_id, options, labels, conditions, structure, sort_order)
VALUES (
    UUID_TO_BIN(:uuid),
    :code,
    (SELECT id FROM pim_catalog_attribute WHERE pim_catalog_attribute.code=:target),
    JSON_OBJECT('delimiter', :delimiter, 'text_transformation', :text_transformation),
    :labels,
    :conditions,
    :structure,
    (SELECT COUNT(1) FROM (
        SELECT * FROM pim_catalog_identifier_generator
    ) AS pcig)
);
SQL;

        try {
            $this->connection->executeStatement($query, $parameters);
        } catch (Exception $e) {
            throw new UnableToSaveIdentifierGeneratorException(\sprintf('Cannot save the identifier generator "%s"', $identifierGenerator->code()->asString()), 0, $e);
        }
    }

    public function update(IdentifierGenerator $identifierGenerator): void
    {
        $parameters = [
            'code' => $identifierGenerator->code()->asString(),
            'target' => $identifierGenerator->target()->asString(),
            'delimiter' => $identifierGenerator->delimiter()->asString(),
            'labels' => \json_encode($identifierGenerator->labelCollection()->normalize()),
            'conditions' => \json_encode($identifierGenerator->conditions()->normalize()),
            'structure' => \json_encode($identifierGenerator->structure()->normalize()),
            'text_transformation' => $identifierGenerator->textTransformation()->normalize(),
        ];

        $query = <<<SQL
UPDATE pim_catalog_identifier_generator SET
    target_id=(SELECT id FROM pim_catalog_attribute WHERE pim_catalog_attribute.code=:target),
    options=JSON_OBJECT('delimiter', :delimiter, 'text_transformation', :text_transformation),
    labels=:labels,
    conditions=:conditions,
    structure=:structure
WHERE pim_catalog_identifier_generator.code=:code;
SQL;

        try {
            $this->connection->executeStatement($query, $parameters);
        } catch (Exception $e) {
            throw new UnableToUpdateIdentifierGeneratorException(\sprintf('Cannot update the identifier generator "%s"', $identifierGenerator->code()->asString()), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $identifierGeneratorCode): IdentifierGenerator
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

    /**
     * {@inheritdoc}
     */
    public function getAll(): array
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
ORDER BY pim_catalog_identifier_generator.sort_order ASC
SQL;

        $stmt = $this->connection->prepare($sql);

        try {
            $result = $stmt->executeQuery()->fetchAllAssociative();
        } catch (DriverException) {
            throw new UnableToFetchIdentifierGeneratorException('Cannot fetch identifiers generators');
        }

        return \array_map(fn ($data) => $this->fromDatabaseToModel($data), $result);
    }

    /**
     * @param array<mixed> $result
     */
    private function fromDatabaseToModel(array $result): IdentifierGenerator
    {
        Assert::string($result['uuid']);
        Assert::string($result['code']);
        Assert::string($result['conditions']);
        Assert::isList(\json_decode($result['conditions'], true));
        Assert::string($result['structure']);
        Assert::isList(\json_decode($result['structure'], true));
        Assert::string($result['labels']);
        Assert::isArray(\json_decode($result['labels'], true));
        Assert::string($result['target']);
        Assert::string($result['options']);
        $options = \json_decode($result['options'], true);
        Assert::isArray($options);
        Assert::keyExists($options, 'delimiter');
        Assert::nullOrString($options['delimiter']);
        Assert::keyExists($options, 'text_transformation');
        Assert::string($options['text_transformation']);

        return new IdentifierGenerator(
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

    /**
     * {@inheritdoc}
     */
    public function getNextId(): IdentifierGeneratorId
    {
        return IdentifierGeneratorId::fromString(Uuid::uuid4()->toString());
    }

    public function count(): int
    {
        return \intval($this->connection->fetchOne('SELECT COUNT(1) FROM pim_catalog_identifier_generator'));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $identifierGeneratorCode): void
    {
        $this->get($identifierGeneratorCode);

        $sql = <<<SQL
UPDATE pim_catalog_identifier_generator
SET sort_order = sort_order - 1
WHERE sort_order > (
    SELECT pcig.sort_order
    FROM (
        SELECT * FROM pim_catalog_identifier_generator
    ) AS pcig
    WHERE pcig.code=:code
);
DELETE FROM pim_catalog_identifier_generator
WHERE code=:code
LIMIT 1;
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam('code', $identifierGeneratorCode, \PDO::PARAM_STR);

        try {
            $stmt->executeQuery();
        } catch (DriverException) {
            throw new UnableToDeleteIdentifierGeneratorException(\sprintf('Cannot delete the identifier generator "%s"', $identifierGeneratorCode));
        }
    }
}
