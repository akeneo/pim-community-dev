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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifierMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Doctrine implementation of the identifiers mapping repository.
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingRepository implements IdentifiersMappingRepositoryInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(EntityManagerInterface $em, AttributeRepositoryInterface $attributeRepository)
    {
        $this->em = $em;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function save(IdentifiersMapping $identifiersMapping): void
    {
        $this->em->beginTransaction();

        $tableName = $this->em->getClassMetadata(IdentifierMapping::class)->getTableName();
        $this->em->getConnection()->executeQuery(sprintf('DELETE FROM %s', $tableName));

        foreach ($identifiersMapping as $identifier) {
            $this->em->persist($identifier);
        }

        $this->em->flush();
        $this->em->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function find(): IdentifiersMapping
    {
        $identifierMappings = $this->em->getRepository(IdentifierMapping::class)->findAll();
        $mappedAttributes = [];

        foreach ($identifierMappings as $identifierMapping) {
            if (!empty($identifierMapping->getAttributeCode())) {
                $attribute = $this->attributeRepository->findOneByIdentifier($identifierMapping->getAttributeCode());
                $mappedAttributes[$identifierMapping->getFranklinCode()] = $attribute;
            }
        }

        return new IdentifiersMapping($mappedAttributes);
    }
}
