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

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeRequired;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\AbstractAttributeDetails;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\ImageAttributeDetails;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\TextAttributeDetails;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindAttributesDetails implements FindAttributesDetailsInterface
{
    /** @var Connection */
    private $sqlConnection;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * @return AbstractAttributeDetails[]
     */
    public function withEnrichedEntityIdentifier(EnrichedEntityIdentifier $enrichedEntityIdentifier): array
    {
        $results = $this->fetchResult($enrichedEntityIdentifier);

        return $this->hydrateAttributesDetails($results);
    }

    private function fetchResult(EnrichedEntityIdentifier $enrichedEntityIdentifier): array
    {
        $query = <<<SQL
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
            $query,
            ['enriched_entity_identifier' => (string) $enrichedEntityIdentifier]
        );
        $result = $statement->fetchAll();

        return !$result ? [] : $result;
    }

    /**
     * @return AbstractAttributeDetails[]
     */
    private function hydrateAttributesDetails(array $results): array
    {
        $recordDetails = [];
        foreach ($results as $result) {
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

                $textAttributeDetails = new TextAttributeDetails();
                $textAttributeDetails->identifier = AttributeIdentifier::create($result['enriched_entity_identifier'], $result['identifier']);
                $textAttributeDetails->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier);
                $textAttributeDetails->code = AttributeCode::fromString($code);
                $textAttributeDetails->order = AttributeOrder::fromInteger($order);
                $textAttributeDetails->labels = LabelCollection::fromArray($labels);
                $textAttributeDetails->required = AttributeRequired::fromBoolean($required);
                $textAttributeDetails->valuePerChannel = AttributeValuePerChannel::fromBoolean($valuePerChannel);
                $textAttributeDetails->valuePerLocale = AttributeValuePerLocale::fromBoolean($valuePerLocale);
                $textAttributeDetails->maxLength = AttributeMaxLength::fromInteger($maxLength);

                $recordDetails[] = $textAttributeDetails;
            } elseif ('image' === $result['attribute_type']) {
                $maxFileSize = $additionnalProperties['max_file_size'];
                $extensions = $additionnalProperties['allowed_extensions'];

                $imageAttributeDetails = new ImageAttributeDetails();
                $imageAttributeDetails->identifier = AttributeIdentifier::create($result['enriched_entity_identifier'], $result['identifier']);
                $imageAttributeDetails->enrichedEntityIdentifier = EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier);
                $imageAttributeDetails->code = AttributeCode::fromString($code);
                $imageAttributeDetails->order = AttributeOrder::fromInteger($order);
                $imageAttributeDetails->labels = LabelCollection::fromArray($labels);
                $imageAttributeDetails->required = AttributeRequired::fromBoolean($required);
                $imageAttributeDetails->valuePerChannel = AttributeValuePerChannel::fromBoolean($valuePerChannel);
                $imageAttributeDetails->valuePerLocale = AttributeValuePerLocale::fromBoolean($valuePerLocale);
                $imageAttributeDetails->maxFileSize = AttributeMaxFileSize::fromString($maxFileSize);
                $imageAttributeDetails->allowedExtensions = AttributeAllowedExtensions::fromList($extensions);

                $recordDetails[] = $imageAttributeDetails;
            } else {
                throw new \LogicException(
                    sprintf('Only attribute types "text" or "image" are supported, "%s" given', $result['attribute_type']
                    )
                );
            }
        }

        return $recordDetails;
    }
}
