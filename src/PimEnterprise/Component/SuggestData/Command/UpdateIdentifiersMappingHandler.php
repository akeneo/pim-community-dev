<?php

declare(strict_types=1);

namespace PimEnterprise\Component\SuggestData\Command;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
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

        $identifiers = $this->replaceAttributeCodesByAttributes($identifiers);

        $identifiersMapping = new IdentifiersMapping($identifiers);

        $this->identifiersMappingRepository->save($identifiersMapping);
    }

    /**
     * @param array $identifiers
     *
     * @return array
     */
    private function replaceAttributeCodesByAttributes(array $identifiers): array
    {
        foreach ($identifiers as $pimAiCode => $attributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

            if (! $attribute instanceof AttributeInterface) {
                throw new \InvalidArgumentException('Some attributes for the identifiers mapping don\'t exist');
            }

            $identifiers[$pimAiCode] = $attribute;
        }

        return $identifiers;
    }
}
