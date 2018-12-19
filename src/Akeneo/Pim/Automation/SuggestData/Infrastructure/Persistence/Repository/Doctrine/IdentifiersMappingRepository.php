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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifierMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
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

    /**
     * @param EntityManagerInterface $em
     * @param AttributeRepositoryInterface $attributeRepository
     */
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
        $identifiers = $this->em->getRepository(IdentifierMapping::class)->findAll();

        $identifiersMapping = new IdentifiersMapping();

        foreach ($identifiers as $identifier) {
            $identifiersMapping->map($identifier->getFranklinCode(), $identifier->getAttribute());
        }

        return $identifiersMapping;
    }
}
