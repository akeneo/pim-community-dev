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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\IdentifiersMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Repository\ProductSubscriptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

/**
 * Handles the saveIdentifiersMapping command.
 * Validates that all attributes exist and creates an IdentifiersMapping entity to save it.
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SaveIdentifiersMappingHandler
{
    /** @var array */
    private const ALLOWED_ATTRIBUTE_TYPES_AS_IDENTIFIER = [
        AttributeTypes::TEXT,
        AttributeTypes::IDENTIFIER,
    ];

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /** @var IdentifiersMappingProviderInterface */
    private $identifiersMappingProvider;

    /** @var ProductSubscriptionRepositoryInterface */
    private $subscriptionRepository;

    /**
     * @param AttributeRepositoryInterface $attributeRepository
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param IdentifiersMappingProviderInterface $identifiersMappingProvider
     * @param ProductSubscriptionRepositoryInterface $subscriptionRepository
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        IdentifiersMappingProviderInterface $identifiersMappingProvider,
        ProductSubscriptionRepositoryInterface $subscriptionRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->identifiersMappingProvider = $identifiersMappingProvider;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @param SaveIdentifiersMappingCommand $command
     *
     * @throws InvalidMappingException
     * @throws DataProviderException
     */
    public function handle(SaveIdentifiersMappingCommand $command): void
    {
        $identifiers = $command->getIdentifiersMapping();
        $identifiers = $this->replaceAttributeCodesByAttributes($identifiers);

        $this->validateMappedIdentifiers($identifiers);
        $this->validateThatBrandAndMpnAreNotSavedAlone($identifiers);

        $identifiersMapping = $this->identifiersMappingRepository->find();

        foreach ($identifiers as $franklinIdentifier => $pimAttribute) {
            $identifiersMapping->map($franklinIdentifier, $pimAttribute);
        }

        $this->identifiersMappingProvider->saveIdentifiersMapping($identifiersMapping);
        $this->subscriptionRepository->emptySuggestedData();
        $this->identifiersMappingRepository->save($identifiersMapping);
    }

    /**
     * @param array $identifiers
     *
     * @throws \InvalidArgumentException If attribute does not exist
     *
     * @return array
     */
    private function replaceAttributeCodesByAttributes(array $identifiers): array
    {
        foreach ($identifiers as $franklinCode => $attributeCode) {
            $identifiers[$franklinCode] = null;
            if (null !== $attributeCode) {
                $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

                if (!$attribute instanceof AttributeInterface) {
                    throw new \InvalidArgumentException(
                        sprintf('Attribute "%s" does not exist', $attributeCode)
                    );
                }

                $identifiers[$franklinCode] = $attribute;
            }
        }

        return $identifiers;
    }

    /**
     * @param array $identifiers
     *
     * @throws InvalidMappingException
     */
    private function validateMappedIdentifiers(array $identifiers): void
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

            if ($attribute->isLocalizable()) {
                throw InvalidMappingException::localizableAttributeNotAllowed($attribute->getCode());
            }

            if ($attribute->isScopable()) {
                throw InvalidMappingException::scopableAttributeNotAllowed($attribute->getCode());
            }

            if ($attribute->isLocaleSpecific()) {
                throw InvalidMappingException::localeSpecificAttributeNotAllowed($attribute->getCode());
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
