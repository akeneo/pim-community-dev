<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Connector\Writer\File\Flat;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\FlatFileHeader;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\GenerateFlatHeadersFromFamilyCodesInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

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
        $user = $this->tokenStorage->getToken()->getUser();
        Assert::implementsInterface($user, UserInterface::class);
        $userId = $user->getId();

        if (null === $userId) {
            return ($this->generateHeadersWithoutPermission)($familyCodes, $channelCode, $localeCodes);
        }

        $channelCurrencyCodes = $this->fetchChannelCurrencyCodes($channelCode);
        $grantedLocaleCodes = $this->fetchGrantedLocaleCodes($localeCodes, $userId);

        $attributesData = $this->fetchGrantedAttributesData($familyCodes, $userId);

        $headers = [];
        foreach ($attributesData as $attributeData) {
            $headers[] = FlatFileHeader::buildFromAttributeData(
                $attributeData["code"],
                $attributeData["attribute_type"],
                ('1' === $attributeData["is_scopable"]),
                $channelCode,
                ('1' === $attributeData["is_localizable"]),
                $grantedLocaleCodes,
                $channelCurrencyCodes,
                null !== $attributeData['specific_to_locales'] ? json_decode($attributeData['specific_to_locales'], true) : []
            );
        }

        return $headers;
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
        )->fetchFirstColumn();
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
        )->fetchFirstColumn();
    }

    /**
     * Fetch granted attributes data from the DB
     */
    private function fetchGrantedAttributesData(array $familyCodes, int $userId): array
    {
        $attributesDataSql = <<<SQL
            WITH attribute_specific_to_locales as (
                 SELECT attribute_id, JSON_ARRAYAGG(l.code) AS specific_to_locales
                 FROM pim_catalog_locale l
                 JOIN pim_catalog_attribute_locale al ON al.locale_id = l.id
                 GROUP BY al.attribute_id
            )
            SELECT a.code,
                   a.is_scopable,
                   a.is_localizable,
                   a.attribute_type,
                   astl.specific_to_locales
            FROM pim_catalog_attribute a
              LEFT JOIN attribute_specific_to_locales astl ON astl.attribute_id = a.id
              JOIN pimee_security_attribute_group_access aga ON aga.attribute_group_id = a.group_id
              JOIN oro_user_access_group ug ON ug.group_id = aga.user_group_id
            WHERE ug.user_id = :userId
              AND aga.view_attributes = 1
              AND a.id IN (
                SELECT fa.attribute_id
                FROM pim_catalog_family f
                JOIN pim_catalog_family_attribute fa ON fa.family_id = f.id
                WHERE f.code IN (:familyCodes)
              )
            GROUP BY a.id;
SQL;

        return $this->connection->executeQuery(
            $attributesDataSql,
            ['familyCodes' => $familyCodes, 'userId' => $userId],
            ['familyCodes' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();
    }
}
