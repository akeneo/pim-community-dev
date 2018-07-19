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

namespace Akeneo\Pim\Automation\SuggestData\Component\Command;

use Akeneo\Pim\Automation\SuggestData\Component\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

/**
 * Handles the UpdateIdentifiersMapping command
 *
 * Validates that all attributes exist and creates an IdentifiersMapping entity to save it
 */
class UpdateIdentifiersMappingHandler
{
    private $attributeRepository;
    private $identifiersMappingRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository, IdentifiersMappingRepositoryInterface $identifiersMappingRepository)
    {
        $this->attributeRepository = $attributeRepository;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * @param UpdateIdentifiersMappingCommand $updateIdentifiersMappingCommand
     */
    public function handle(UpdateIdentifiersMappingCommand $updateIdentifiersMappingCommand): void
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
