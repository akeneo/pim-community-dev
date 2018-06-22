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
     * @inheritDoc
     */
    public function save(IdentifiersMapping $identifiersMapping): void
    {
        foreach ($identifiersMapping as $pimAiCode => $pimAttributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($pimAttributeCode);

            $identifier = $this->em->getRepository(IdentifierMapping::class)->findOneBy(['pimAiCode' => $pimAiCode]);
            if (! $identifier instanceof IdentifierMapping) {
                $identifier = new IdentifierMapping();
                $identifier->pimAiCode = $pimAiCode;
                $identifier->attribute = $attribute;
            } else {
                $identifier->attribute = $attribute;
            }

            $this->em->persist($identifier);
        }
        $this->em->flush();
    }

    /**
     * @inheritDoc
     */
    public function findAll(): IdentifiersMapping
    {
        $identifiers = $this->em->getRepository(IdentifierMapping::class)->findAll();

        $identifiersArray = [];
        foreach ($identifiers as $identifier) {
            $identifiersArray[$identifier->pimAiCode] = $identifier->attribute->getCode();
        }

        return new IdentifiersMapping($identifiersArray);
    }
}
