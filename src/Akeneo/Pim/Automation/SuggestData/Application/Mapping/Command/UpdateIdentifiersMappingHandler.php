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

namespace Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command;

use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

/**
 * Handles the UpdateIdentifiersMapping command.
 * Validates that all attributes exist and creates an IdentifiersMapping entity to save it.
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class UpdateIdentifiersMappingHandler
{
    /** @var array */
    private const ALLOWED_ATTRIBUTE_TYPES_AS_IDENTIFIER = [
        AttributeTypes::TEXT,
        AttributeTypes::OPTION_SIMPLE_SELECT,
        AttributeTypes::IDENTIFIER,
        AttributeTypes::NUMBER,
    ];

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * @param UpdateIdentifiersMappingCommand $updateIdentifiersMappingCommand
     *
     * @throws InvalidMappingException
     */
    public function handle(UpdateIdentifiersMappingCommand $updateIdentifiersMappingCommand): void
    {
        $identifiers = $updateIdentifiersMappingCommand->getIdentifiersMapping();
        $identifiers = $this->replaceAttributeCodesByAttributes($identifiers);

        $this->validateAttributeTypes($identifiers);
        $this->validateThatBrandAndMpnAreNotSavedAlone($identifiers);

        $identifiersMapping = new IdentifiersMapping($identifiers);
        $this->identifiersMappingRepository->save($identifiersMapping);
    }

    /**
     * @param array $identifiers
     *
     * @return array
     *
     * @throws InvalidMappingException If attribute does not exist
     */
    private function replaceAttributeCodesByAttributes(array $identifiers): array
    {
        foreach ($identifiers as $pimAiCode => $attributeCode) {
            $identifiers[$pimAiCode] = null;
            if (null !== $attributeCode) {
                $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

                if (!$attribute instanceof AttributeInterface) {
                    throw InvalidMappingException::attributeNotFound($attributeCode, static::class, $pimAiCode);
                }

                $identifiers[$pimAiCode] = $attribute;
            }
        }

        return $identifiers;
    }

    /**
     * @param array $identifiers
     */
    private function validateAttributeTypes(array $identifiers): void
    {
        foreach ($identifiers as $identifier => $attribute) {
            if (empty($attribute)) {
                continue;
            }

            if (!in_array($attribute->getType(), static::ALLOWED_ATTRIBUTE_TYPES_AS_IDENTIFIER)) {
                throw InvalidMappingException::attributeType(
                    static::class,
                    $identifier
                );
            }
        }
    }

    /**
     * @param array $identifiers
     *
     * @throws InvalidMappingException
     */
    private function validateThatBrandAndMpnAreNotSavedAlone(array $identifiers): void
    {
        $isBrandDefined = isset($identifiers['brand']) && $identifiers['brand'] instanceof AttributeInterface;
        $isMpnDefined = isset($identifiers['mpn']) && $identifiers['mpn'] instanceof AttributeInterface;

        if ($isBrandDefined xor $isMpnDefined) {
            throw InvalidMappingException::mandatoryAttributeMapping(
                static::class,
                $isBrandDefined ? 'mpn' : 'brand'
            );
        }
    }
}
