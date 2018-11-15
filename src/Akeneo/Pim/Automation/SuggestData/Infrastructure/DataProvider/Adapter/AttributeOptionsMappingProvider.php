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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AttributeOptionsMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FranklinAttributeId;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionsMapping as ReadAttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeOptionsMapping as WriteAttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\OptionsMapping\OptionsMappingInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Converter\AttributeOptionsMappingConverter;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\AttributeOptionsMappingNormalizer;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AttributeOptionsMappingProvider implements AttributeOptionsMappingProviderInterface
{
    /** @var OptionsMappingInterface */
    private $api;

    /** @var Token */
    private $token;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /**
     * @param OptionsMappingInterface $api
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(
        OptionsMappingInterface $api,
        ConfigurationRepositoryInterface $configurationRepository
    ) {
        $this->api = $api;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeOptionsMapping(
        FamilyCode $familyCode,
        FranklinAttributeId $franklinAttributeId
    ): ReadAttributeOptionsMapping {
        $this->api->setToken($this->getToken());
        $franklinOptionsMapping = $this
            ->api
            ->fetchByFamilyAndAttribute((string) $familyCode, (string) $franklinAttributeId);

        $converter = new AttributeOptionsMappingConverter();

        return $converter->clientToApplication(
            (string) $familyCode,
            (string) $franklinAttributeId,
            $franklinOptionsMapping
        );
    }

    /**
     * {@inheritdoc}
     */
    public function saveAttributeOptionsMapping(
        FamilyCode $familyCode,
        FranklinAttributeId $franklinAttributeId,
        WriteAttributeOptionsMapping $attributeOptionsMapping
    ): void {
        $this->api->setToken($this->getToken());
        $normalizer = new AttributeOptionsMappingNormalizer();
        $normalizedMapping = $normalizer->normalize($attributeOptionsMapping);

        $this->api->update(
            (string) $familyCode,
            (string) $franklinAttributeId,
            $normalizedMapping
        );
    }

    /**
     * @return string
     */
    private function getToken(): string
    {
        if (null === $this->token) {
            $config = $this->configurationRepository->find();
            if ($config instanceof Configuration) {
                $this->token = $config->getToken();
            }
        }

        return (string) $this->token;
    }
}
