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
        $identifiersMappingToSave = [];
        foreach ($identifiersMapping as $franklinCode => $attribute) {
            $identifierMapping = $this->em
                ->getRepository(IdentifierMapping::class)
                ->findOneBy(['franklinCode' => $franklinCode]);

            if (!$identifierMapping instanceof IdentifierMapping) {
                $identifierMapping = new IdentifierMapping($franklinCode, null);
            }
            $identifierMapping->setAttribute(null);

            $this->em->persist($identifierMapping);
            $identifiersMappingToSave[$franklinCode] = $identifierMapping;
        }
        $this->em->flush();

        foreach ($identifiersMappingToSave as $franklinCode => $identifierMapping) {
            $identifierMapping->setAttribute($identifiersMapping->getIterator()[$franklinCode]);

            $this->em->persist($identifierMapping);
        }
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function find(): IdentifiersMapping
    {
        $identifiers = $this->em->getRepository(IdentifierMapping::class)->findAll();

        $identifiersArray = array_fill_keys(IdentifiersMapping::FRANKLIN_IDENTIFIERS, null);
        foreach ($identifiers as $identifier) {
            $identifiersArray[$identifier->getFranklinCode()] = $identifier->getAttribute();
        }

        return new IdentifiersMapping($identifiersArray);
    }
}
