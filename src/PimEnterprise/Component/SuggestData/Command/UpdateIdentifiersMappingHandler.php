<?php

declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\Command;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\SuggestDataBundle\Entity\IdentifierMapping;
use PimEnterprise\Component\SuggestData\Model\IdentifiersMapping;
use PimEnterprise\Component\SuggestData\Repository\IdentifiersMappingRepositoryInterface;

class UpdateIdentifiersMappingHandler
{
    private $attributeRepository;
    private $identifiersMappingRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository, IdentifiersMappingRepositoryInterface $identifiersMappingRepository)
    {
        $this->attributeRepository = $attributeRepository;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * @param UpdateIdentifiersMapping $updateIdentifiersMappingCommand
     */
    public function handle(UpdateIdentifiersMapping $updateIdentifiersMappingCommand): void
    {
        $identifiers = $updateIdentifiersMappingCommand->getIdentifiersMapping();
        $this->validateAttributesExist($identifiers);

        $identifiersMapping = new IdentifiersMapping($identifiers);

        $this->identifiersMappingRepository->save($identifiersMapping);
    }

    /**
     * @param array $attributeIdentifiers
     */
    private function validateAttributesExist(array $attributeIdentifiers): void
    {
        $attributeIdentifiers = array_filter(array_values($attributeIdentifiers));

        $attributes = $this->attributeRepository->findBy(['code' => $attributeIdentifiers]);

        if (count($attributes) !== count($attributeIdentifiers)) {
            throw new \InvalidArgumentException('Some attributes for the identifiers mapping don\'t exist');
        }
    }
}
