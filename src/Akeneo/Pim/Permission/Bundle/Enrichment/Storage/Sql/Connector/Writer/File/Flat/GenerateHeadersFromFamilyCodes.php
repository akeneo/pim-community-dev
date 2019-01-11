<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Connector\Writer\File\Flat;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromFamilyCodesInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GenerateHeadersFromFamilyCodes implements GenerateFlatHeadersFromFamilyCodesInterface
{
    /** @var Connection */
    private $connection;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var GenerateFlatHeadersFromFamilyCodesInterface */
    private $generateHeadersWithoutPermission;

    public function __construct(
        Connection $connection,
        TokenStorageInterface $tokenStorage,
        GenerateFlatHeadersFromFamilyCodesInterface $generateHeadersWithoutPermission
    ) {
        $this->connection = $connection;
        $this->tokenStorage = $tokenStorage;
        $this->generateHeadersWithoutPermission = $generateHeadersWithoutPermission;
    }

    /**
     * Generate all possible headers from the provided family codes
     *
     * @return FlatFileHeader[]
     */
    public function __invoke(
        array $familyCodes,
        string $channelCode,
        array $localeCodes
    ): array {
        $userId = $this->tokenStorage->getToken()->getUser()->getId();

        if (null === $userId) {
            return ($this->generateHeadersWithoutPermission)($familyCodes, $channelCode, $localeCodes);
        }

        $activatedCurrencyCodes = $this->fetchActivatedCurrencyCodes();
        $channelCurrencyCodes = $this->fetchChannelCurrencyCodes($channelCode);
        $grantedLocaleCodes = $this->fetchGrantedLocaleCodes($localeCodes, $userId);

        $attributesData = $this->fetchGrantedAttributesData($familyCodes, $userId);

        $headers = [];
        foreach ($attributesData as $attributeData) {
            $headers[] = FlatFileHeader::buildFromAttributeData(
                $attributeData["code"],
                $attributeData["attribute_type"],
                ("1" === $attributeData["is_scopable"]),
                $channelCode,
                ("1" === $attributeData["is_localizable"]),
                $grantedLocaleCodes,
                $channelCurrencyCodes,
                $activatedCurrencyCodes,
                null !== $attributeData['specific_to_locales'] ? json_decode($attributeData['specific_to_locales'], true) : []
            );
        }

        return $headers;
    }

    /**
     * Fetch all activated currencies from the DB
     */
    private function fetchActivatedCurrencyCodes(): array
    {
        return $this->connection->executeQuery(
            "SELECT code FROM pim_catalog_currency WHERE is_activated = 1"
        )->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    /**
     * Fetch all currencies related to the channel from the DB
     */
    private function fetchChannelCurrencyCodes(string $channelCode): array
    {
        $channelCurrencyCodesSql = <<<SQL
            SELECT currency.code
            FROM pim_catalog_channel channel
              JOIN pim_catalog_channel_currency cc ON cc.channel_id = channel.id
              JOIN pim_catalog_currency currency ON currency.id = cc.currency_id
            WHERE channel.code = :channelCode
SQL;
        return $this->connection->executeQuery(
            $channelCurrencyCodesSql,
            ['channelCode' => $channelCode]
        )->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    /**
     * Fetch locale codes that are granted to the user amongst the one
     * provided in parameters
     */
    private function fetchGrantedLocaleCodes(array $localeCodes, int $userId): array
    {
        $grantedLocaleCodesSql = <<<SQL
            SELECT DISTINCT(locale.code)
            FROM pim_catalog_locale locale
             JOIN pimee_security_locale_access la ON la.locale_id = locale.id
             JOIN oro_user_access_group ug ON ug.group_id = la.user_group_id
            WHERE locale.code in (:localeCodes)
              AND ug.user_id = :userId
              AND la.view_products = 1
SQL;
        return $this->connection->executeQuery(
            $grantedLocaleCodesSql,
            ['localeCodes' => $localeCodes, 'userId' => $userId],
            ['localeCodes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    /**
     * Fetch granted attributes data from the DB
     */
    private function fetchGrantedAttributesData(array $familyCodes, int $userId): array
    {
        $attributesDataSql = <<<SQL
            SELECT a.code,
                   a.is_scopable,
                   a.is_localizable,
                   a.attribute_type,
                   (
                     SELECT JSON_ARRAYAGG(l.code)
                     FROM pim_catalog_locale l
                     JOIN pim_catalog_attribute_locale al ON al.locale_id = l.id
                     WHERE al.attribute_id = a.id
                   ) AS specific_to_locales
            FROM pim_catalog_family f
              JOIN pim_catalog_family_attribute fa ON fa.family_id = f.id
              JOIN pim_catalog_attribute a ON a.id = fa.attribute_id
              JOIN pimee_security_attribute_group_access aga ON aga.attribute_group_id = a.group_id
              JOIN oro_user_access_group ug ON ug.group_id = aga.user_group_id
            WHERE f.code IN (:familyCodes)
              AND ug.user_id = :userId
              AND aga.view_attributes = 1
            GROUP BY a.id;
SQL;

        return $this->connection->executeQuery(
            $attributesDataSql,
            ['familyCodes' => $familyCodes, 'userId' => $userId],
            ['familyCodes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAll();
    }
}
