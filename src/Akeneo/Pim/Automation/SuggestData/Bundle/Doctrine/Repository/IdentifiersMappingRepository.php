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

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Doctrine\Repository;

use Akeneo\Pim\Automation\SuggestData\Bundle\Entity\IdentifierMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\IdentifiersMappingRepositoryInterface;
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
     * @param EntityManagerInterface       $em
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
        foreach ($identifiersMapping as $pimAiCode => $attribute) {
            $identifierMapping = $this->em
                ->getRepository(IdentifierMapping::class)
                ->findOneBy(['pimAiCode' => $pimAiCode]);

            if (!$identifierMapping instanceof IdentifierMapping) {
                $identifierMapping = new IdentifierMapping($pimAiCode, $attribute);
            }
            $identifierMapping->setAttribute($attribute);

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

        $identifiersArray = array_fill_keys(IdentifiersMapping::PIM_AI_IDENTIFIERS, null);
        foreach ($identifiers as $identifier) {
            $identifiersArray[$identifier->getPimAiCode()] = $identifier->getAttribute();
        }

        return new IdentifiersMapping($identifiersArray);
    }
}
