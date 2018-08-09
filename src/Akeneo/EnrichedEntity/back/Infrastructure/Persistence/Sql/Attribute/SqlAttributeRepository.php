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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\ImageAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\Intl\Exception\NotImplementedException;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlAttributeRepository implements AttributeRepositoryInterface
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function create(AbstractAttribute $attribute): void
    {
        $normalizedAttribute = $attribute->normalize();
        $additionalProperties = $this->getAdditionalOptions($normalizedAttribute);
        $insert = <<<SQL
        INSERT INTO akeneo_enriched_entity_attribute (
            identifier,
            enriched_entity_identifier,
            labels,
            attribute_type,
            attribute_order,
            required,
            value_per_channel,
            value_per_locale,
            additional_properties
        )
        VALUES (
            :identifier,
            :enriched_entity_identifier,
            :labels,
            :attribute_type,
            :attribute_order,
            :required,
            :value_per_channel,
            :value_per_locale,
            :additional_properties
        );
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier' => $normalizedAttribute['code'],
                'enriched_entity_identifier' => $normalizedAttribute['enriched_entity_identifier'],
                'labels' => json_encode($normalizedAttribute['labels']),
                'attribute_type' => $normalizedAttribute['type'],
                'attribute_order' => $normalizedAttribute['order'],
                'required' => $normalizedAttribute['required'],
                'value_per_channel' => $normalizedAttribute['value_per_channel'],
                'value_per_locale' => $normalizedAttribute['value_per_locale'],
                'additional_properties' => json_encode($additionalProperties),
            ],
            [
                'required' => \Doctrine\DBAL\Types\Type::getType('boolean'),
                'value_per_channel' => \Doctrine\DBAL\Types\Type::getType('boolean'),
                'value_per_locale' => \Doctrine\DBAL\Types\Type::getType('boolean'),
            ]
        );
        if ($affectedRows > 1) {
            throw new \RuntimeException(
                sprintf('Expected to create one attribute, but %d rows were affected', $affectedRows)
            );
        }
    }

    public function update(AbstractAttribute $attribute): void
    {
        throw new NotImplementedException('not implemented');
    }

    /**
     * @throws AttributeNotFoundException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getByIdentifier(AttributeIdentifier $identifier): AbstractAttribute
    {
        $fetch = <<<SQL
        SELECT
            identifier,
            enriched_entity_identifier,
            labels,
            attribute_type,
            attribute_order,
            required,
            value_per_channel,
            value_per_locale,
            additional_properties
        FROM akeneo_enriched_entity_attribute
        WHERE identifier = :identifier AND enriched_entity_identifier = :enriched_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'identifier' => $identifier->getIdentifier(),
                'enriched_entity_identifier' => $identifier->getEnrichedEntityIdentifier(),
            ]
        );
        $result = $statement->fetch();
        $statement->closeCursor();

        if (!$result) {
            throw AttributeNotFoundException::withIdentifier($identifier);
        }

        return $this->hydrateAttribute($result);
    }

    /**
     * @param EnrichedEntityIdentifier $enrichedEntityIdentifier
     *
     * @return AbstractAttribute[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByEnrichedEntity(EnrichedEntityIdentifier $enrichedEntityIdentifier): array
    {
        $fetch = <<<SQL
        SELECT
            identifier,
            enriched_entity_identifier,
            labels,
            attribute_type,
            attribute_order,
            required,
            value_per_channel,
            value_per_locale,
            additional_properties
        FROM akeneo_enriched_entity_attribute
        WHERE enriched_entity_identifier = :enriched_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'enriched_entity_identifier' => $enrichedEntityIdentifier,
            ]
        );
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();

        $attributes = [];
        foreach ($results as $result) {
            $attributes[] = $this->hydrateAttribute($result);
        }

        return $attributes;
    }

    private function getAdditionalOptions(array $normalizedAttribute): array
    {
        unset($normalizedAttribute['identifier']);
        unset($normalizedAttribute['enriched_entity_identifier']);
        unset($normalizedAttribute['code']);
        unset($normalizedAttribute['labels']);
        unset($normalizedAttribute['order']);
        unset($normalizedAttribute['required']);
        unset($normalizedAttribute['value_per_channel']);
        unset($normalizedAttribute['value_per_locale']);
        unset($normalizedAttribute['type']);

        return $normalizedAttribute;
    }

    /**
     * This method should probably split into hydrators.
     *
     * One idea could be:
     * AbstractAttributeHydrator <- hydrates the common properties
     * ^
     * TextAttributeHydrator <- hydrates attributes specific to the text attribute and creates an instance of attribute.
     */
    private function hydrateAttribute(array $result): AbstractAttribute
    {
        $code = $result['identifier'];
        $enrichedEntityIdentifier = $result['enriched_entity_identifier'];
        $labels = json_decode($result['labels'], true);
        $order = (int) $result['attribute_order'];
        $required = (bool) $result['required'];
        $valuePerChannel = (bool) $result['value_per_channel'];
        $valuePerLocale = (bool) $result['value_per_locale'];
        $additionnalProperties = json_decode($result['additional_properties'], true);

        if ('text' === $result['attribute_type']) {
            $maxLength = (int) $additionnalProperties['max_length'];

            return TextAttribute::create(
                AttributeIdentifier::create($result['enriched_entity_identifier'], $result['identifier']),
                EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier),
                AttributeCode::fromString($code),
                LabelCollection::fromArray($labels),
                AttributeOrder::fromInteger($order),
                AttributeRequired::fromBoolean($required),
                AttributeValuePerChannel::fromBoolean($valuePerChannel),
                AttributeValuePerLocale::fromBoolean($valuePerLocale),
                AttributeMaxLength::fromInteger($maxLength)
            );
        }

        if ('image' === $result['attribute_type']) {
            $maxFileSize = $additionnalProperties['max_file_size'];
            $extensions = $additionnalProperties['allowed_extensions'];

            return ImageAttribute::create(
                AttributeIdentifier::create($result['enriched_entity_identifier'], $result['identifier']),
                EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier),
                AttributeCode::fromString($code),
                LabelCollection::fromArray($labels),
                AttributeOrder::fromInteger($order),
                AttributeRequired::fromBoolean($required),
                AttributeValuePerChannel::fromBoolean($valuePerChannel),
                AttributeValuePerLocale::fromBoolean($valuePerLocale),
                AttributeMaxFileSize::fromString($maxFileSize),
                AttributeAllowedExtensions::fromList($extensions)
            );
        }

        throw new \LogicException(
            sprintf('Only attribute types "text" or "image" are supported, "%s" given', $result['attribute_type']
            )
        );
    }

    /**
     * @throws AttributeNotFoundException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function deleteByIdentifier(AttributeIdentifier $identifier): void
    {
        $sql = <<<SQL
        DELETE FROM akeneo_enriched_entity_attribute
        WHERE identifier = :identifier AND enriched_entity_identifier = :enriched_entity_identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $sql,
            [
                'identifier' => $identifier->getIdentifier(),
                'enriched_entity_identifier' => $identifier->getEnrichedEntityIdentifier(),
            ]
        );
        if (1 !== $affectedRows) {
            throw AttributeNotFoundException::withIdentifier($identifier);
        }
    }
}
