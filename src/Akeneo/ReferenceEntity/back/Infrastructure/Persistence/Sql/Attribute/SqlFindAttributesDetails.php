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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeDetails;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Locale\FindActivatedLocalesInterface;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindAttributesDetails implements FindAttributesDetailsInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var FindActivatedLocalesInterface  */
    private $findActivatedLocales;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(Connection $sqlConnection, FindActivatedLocalesInterface $findActivatedLocales)
    {
        $this->sqlConnection = $sqlConnection;
        $this->findActivatedLocales = $findActivatedLocales;
    }

    /**
     * @return AttributeDetails[]
     */
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $results = $this->fetchResult($referenceEntityIdentifier);

        return $this->hydrateAttributesDetails($results);
    }

    private function fetchResult(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $query = <<<SQL
        SELECT
            identifier,
            code,
            reference_entity_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            value_per_channel,
            value_per_locale,
            additional_properties
        FROM akeneo_reference_entity_attribute
        WHERE reference_entity_identifier = :reference_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            ['reference_entity_identifier' => (string) $referenceEntityIdentifier]
        );
        $result = $statement->fetchAll();

        return !$result ? [] : $result;
    }

    /**
     * @return AttributeDetails[]
     */
    private function hydrateAttributesDetails(array $results): array
    {
        $activatedLocales = ($this->findActivatedLocales)();
        $allAttributeDetails = [];
        foreach ($results as $result) {
            $attributeDetails = new AttributeDetails();
            $attributeDetails->type = $result['attribute_type'];
            $attributeDetails->identifier = $result['identifier'];
            $attributeDetails->referenceEntityIdentifier = $result['reference_entity_identifier'];
            $attributeDetails->code = $result['code'];
            $attributeDetails->order = (int) $result['attribute_order'];
            $attributeDetails->labels = $this->getLabelsByActivatedLocale($result, $activatedLocales);
            $attributeDetails->isRequired = (bool) $result['is_required'];
            $attributeDetails->valuePerChannel = (bool) $result['value_per_channel'];
            $attributeDetails->valuePerLocale = (bool) $result['value_per_locale'];
            $attributeDetails->additionalProperties = json_decode($result['additional_properties'], true);

            $allAttributeDetails[] = $attributeDetails;
        }

        return $allAttributeDetails;
    }

    private function getLabelsByActivatedLocale(array $result, array $activatedLocales): array
    {
        $labels = [];
        foreach (json_decode($result['labels'], true) as $localeCode => $label) {
            if (in_array($localeCode, $activatedLocales)) {
                $labels[$localeCode] = $label;
            }
        }

        return $labels;
    }
}
