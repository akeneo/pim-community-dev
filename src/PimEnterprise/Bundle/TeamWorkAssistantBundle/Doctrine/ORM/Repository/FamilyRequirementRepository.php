<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamWorkAssistantBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\TeamWorkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamWorkAssistant\Repository\FamilyRequirementRepositoryInterface;

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
     */
    public function findRequiredAttributes(ProductInterface $product, ProjectInterface $project)
    {
        $queryBuilder = $this->createQueryBuilder('ar');

        $queryBuilder->select('a.code as attribute_code, g.id as attribute_group_id')
            ->leftJoin('ar.family', 'f')
            ->leftJoin('ar.channel', 'c')
            ->leftJoin('ar.attribute', 'a')
            ->leftJoin('a.group', 'g')
            ->where('f.code = :family_code')
            ->andWhere('c.code = :channel_code')
            ->andWhere('ar.required = :required')
            ->setParameters([
                'family_code'  => $product->getFamily()->getCode(),
                'channel_code' => $project->getChannel()->getCode(),
                'required'     => true,
            ]);

        $familyRequirements = $queryBuilder->getQuery()->getArrayResult();

        $formattedRequirements = [];
        foreach ($familyRequirements as $attribute) {
            $formattedRequirements[$attribute['attribute_group_id']][] = $attribute['attribute_code'];
        }

        return $formattedRequirements;
    }
}
