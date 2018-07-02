<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\Doctrine\Repository;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Entity\IdentifierMapping;
use PimEnterprise\Component\SuggestData\Model\IdentifiersMapping;
use PimEnterprise\Component\SuggestData\Repository\IdentifiersMappingRepositoryInterface;

class IdentifiersMappingRepository implements IdentifiersMappingRepositoryInterface
{
    private $em;
    private $attributeRepository;

    public function __construct(EntityManagerInterface $em, AttributeRepositoryInterface $attributeRepository)
    {
        $this->em = $em;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @inheritdoc
     */
    public function save(IdentifiersMapping $identifiersMapping): void
    {
        foreach ($identifiersMapping as $pimAiCode => $attribute) {
            $identifierMapping = $this->em->getRepository(IdentifierMapping::class)->findOneBy(['pimAiCode' => $pimAiCode]);
            if (! $identifierMapping instanceof IdentifierMapping) {
                $identifierMapping = new IdentifierMapping(null, $pimAiCode, $attribute);
            }
            $identifierMapping->updateAttribute($attribute);

            $this->em->persist($identifierMapping);
        }
        $this->em->flush();
    }

    /**
     * @inheritdoc
     */
    public function findAll(): IdentifiersMapping
    {
        $identifiers = $this->em->getRepository(IdentifierMapping::class)->findAll();

        $identifiersArray = [];
        foreach ($identifiers as $identifier) {
            $identifiersArray[$identifier->getPimAiCode()] = $identifier->getAttribute();
        }

        return new IdentifiersMapping($identifiersArray);
    }
}
