<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use PimEnterprise\Component\ActivityManager\Presenter\PresenterInterface;
use PimEnterprise\Component\ActivityManager\Repository\FamilyRequirementRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\StructuredAttributeRepositoryInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class FamilyRequirementRepository extends EntityRepository implements
    FamilyRequirementRepositoryInterface,
    StructuredAttributeRepositoryInterface
{
    /** @var Connection */
    protected $connection;

    /** @var PresenterInterface */
    protected $familyRequirementPresenter;

    /**
     * @param EntityManagerInterface $em
     * @param PresenterInterface     $familyRequirementPresenter
     * @param string                 $class
     */
    public function __construct(EntityManagerInterface $em, PresenterInterface $familyRequirementPresenter, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));

        $this->connection = $em->getConnection();
        $this->familyRequirementPresenter = $familyRequirementPresenter;
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
     * TODO: use the ORM instead of SQL
     * {@inheritdoc}
     */
    public function getStructuredAttributes($familyCode, $channelId, $localeId)
    {
        $sql = <<<SQL
            SELECT a.code as attribute_code, ag.code as attribute_group_code
            FROM pim_catalog_family family
            JOIN pim_catalog_attribute_requirement ar
                ON ar.family_id = family.id AND ar.required = 1 AND ar.channel_id = 3
            LEFT JOIN pim_catalog_attribute a ON a.id = ar.attribute_id
            LEFT JOIN pim_catalog_attribute_group ag ON a.group_id = ag.id
            WHERE family.code = :familyCode
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('familyCode', $familyCode);
        $stmt->execute();

        return $this->familyRequirementPresenter->present($stmt->fetchAll());
    }

    /**
     * TODO: use the ORM instead of SQL
     *
     * TODO: maybe move into family repo
     *
     * {@inheritdoc}
     */
    public function getFamilyCode($productId)
    {
        $sql = <<<SQL
            SELECT family.code
            FROM pim_catalog_family family
            JOIN pim_catalog_product product
                ON product.family_id = family.id
            WHERE product.id = :productId
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('productId', $productId);
        $stmt->execute();

        return $stmt->fetch();
    }
}
