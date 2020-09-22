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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeDetails;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesInterface;
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
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $results = $this->fetchResult($assetFamilyIdentifier);

        return $this->hydrateAttributesDetails($results);
    }

    private function fetchResult(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $query = <<<SQL
        SELECT
            identifier,
            code,
            asset_family_identifier,
            labels,
            attribute_type,
            attribute_order,
            is_required,
            is_read_only,
            value_per_channel,
            value_per_locale,
            additional_properties
        FROM akeneo_asset_manager_attribute
        WHERE asset_family_identifier = :asset_family_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $query,
            ['asset_family_identifier' => (string) $assetFamilyIdentifier]
        );
        $result = $statement->fetchAll();

        return !$result ? [] : $result;
    }

    /**
     * @return AttributeDetails[]
     */
    private function hydrateAttributesDetails(array $results): array
    {
        $activatedLocales = $this->findActivatedLocales->findAll();
        $allAttributeDetails = [];
        foreach ($results as $result) {
            $labels = json_decode($result['labels'], true);
            $additionalProperties = json_decode($result['additional_properties'], true);
            if (array_key_exists('options', $additionalProperties)) {
                $additionalProperties['options'] = $this->filterActivatedLocaleOptions($additionalProperties['options'], $activatedLocales);
            }

            $attributeDetails = new AttributeDetails();
            $attributeDetails->type = $result['attribute_type'];
            $attributeDetails->identifier = $result['identifier'];
            $attributeDetails->assetFamilyIdentifier = $result['asset_family_identifier'];
            $attributeDetails->code = $result['code'];
            $attributeDetails->order = (int) $result['attribute_order'];
            $attributeDetails->labels = $this->getLabelsByActivatedLocale($labels, $activatedLocales);
            $attributeDetails->isRequired = (bool) $result['is_required'];
            $attributeDetails->isReadOnly = (bool) $result['is_read_only'];
            $attributeDetails->valuePerChannel = (bool) $result['value_per_channel'];
            $attributeDetails->valuePerLocale = (bool) $result['value_per_locale'];
            $attributeDetails->additionalProperties = $additionalProperties;

            $allAttributeDetails[] = $attributeDetails;
        }

        return $allAttributeDetails;
    }


    private function filterActivatedLocaleOptions(array $options, array $activatedLocales)
    {
        return array_map(function ($option) use ($activatedLocales) {
            $option['labels'] = $this->getLabelsByActivatedLocale($option['labels'], $activatedLocales);

            return $option;
        }, $options);
    }

    // @todo merge master: use InactiveLabelFilter::filter
    private function getLabelsByActivatedLocale(array $labels, array $activatedLocales): array
    {
        $result = [];
        foreach ($labels as $localeCode => $label) {
            if (in_array($localeCode, $activatedLocales)) {
                $result[$localeCode] = $label;
            }
        }

        return $result;
    }
}
