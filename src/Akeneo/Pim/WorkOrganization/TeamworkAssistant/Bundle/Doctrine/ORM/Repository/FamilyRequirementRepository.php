<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\FamilyRequirementRepositoryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class FamilyRequirementRepository extends EntityRepository implements FamilyRequirementRepositoryInterface
{
    /**
     * @param EntityManager $em
     * @param string        $class
     */
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * {@inheritdoc}
     */
    public function findAttributeGroupIdentifiers(FamilyInterface $family, ChannelInterface $channel)
    {
        $queryBuilder = $this->createQueryBuilder('ar');

        $queryBuilder->select('DISTINCT g.code')
            ->leftJoin('ar.channel', 'c')
            ->leftJoin('ar.family', 'f')
            ->leftJoin('ar.attribute', 'a')
            ->leftJoin('a.group', 'g')
            ->where('f.code = :family_code')
            ->andWhere('c.code = :channel_code')
            ->andWhere('ar.required = :required')
            ->setParameters([
                'family_code'  => $family->getCode(),
                'channel_code' => $channel->getCode(),
                'required'     => true,
            ]);

        return array_column($queryBuilder->getQuery()->getArrayResult(), 'code');
    }

    /**
     * {@inheritdoc}
     *
     * An attribute is required for a product, channel and locale if
     * - it belongs to the family and is required for this channel
     * - it is not locale_specific OR is locale_specific for this locale
     */
    public function findRequiredAttributes(
        ProductInterface $product,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $connection = $this->_em->getConnection();
        $sql = <<<SQL
SELECT
    JSON_ARRAYAGG(attribute.code) AS attribute_codes,
    attribute_group.id AS attribute_group_id
FROM 
    pim_catalog_attribute_requirement requirement
    INNER JOIN pim_catalog_attribute attribute ON requirement.attribute_id = attribute.id
    INNER JOIN pim_catalog_attribute_group attribute_group ON attribute.group_id = attribute_group.id
    LEFT JOIN pim_catalog_attribute_locale not_locale_specific ON attribute.id = not_locale_specific.attribute_id
    LEFT JOIN pim_catalog_attribute_locale locale_specific ON attribute.id = locale_specific.attribute_id AND locale_specific.locale_id = :localeId
WHERE 
    requirement.family_id = :familyId
    AND requirement.channel_id = :channelId
    AND requirement.required = 1
    AND (
        not_locale_specific.locale_id IS NULL
        OR
        locale_specific.locale_id IS NOT NULL
    )
GROUP BY attribute_group_id
SQL;

        $rows = $connection->fetchAllAssociative(
            $sql,
            [
                'localeId' => $locale->getId(),
                'familyId' => $product->getFamily()->getId(),
                'channelId' => $channel->getId()
            ]
        );

        $formattedRequirements = [];
        foreach ($rows as $row) {
            $formattedRequirements[$row['attribute_group_id']] = json_decode($row['attribute_codes']);
        }

        return $formattedRequirements;
    }
}
