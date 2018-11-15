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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\AttributeMapping as DomainAttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\AttributesMappingResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AttributesMapping\AttributesMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\AttributesMappingNormalizer;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AttributesMappingProvider implements AttributesMappingProviderInterface
{
    /** @var AttributesMappingApiInterface */
    private $api;

    /** @var AttributesMappingNormalizer */
    private $normalizer;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var Token */
    private $token;

    /**
     * @param AttributesMappingApiInterface $api
     * @param AttributesMappingNormalizer $normalizer
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(
        AttributesMappingApiInterface $api,
        AttributesMappingNormalizer $normalizer,
        ConfigurationRepositoryInterface $configurationRepository
    ) {
        $this->api = $api;
        $this->normalizer = $normalizer;
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributesMapping(string $familyCode): AttributesMappingResponse
    {
        $this->api->setToken($this->getToken());
        $apiResponse = $this->api->fetchByFamily($familyCode);

        $attributesMapping = new AttributesMappingResponse();
        foreach ($apiResponse as $attribute) {
            $attribute = new DomainAttributeMapping(
                $attribute->getTargetAttributeCode(),
                $attribute->getTargetAttributeLabel(),
                $attribute->getTargetAttributeType(),
                $attribute->getPimAttributeCode(),
                $this->mapAttributeMappingStatus($attribute->getStatus()),
                $attribute->getSummary()
            );
            $attributesMapping->addAttribute($attribute);
        }

        return $attributesMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function updateAttributesMapping(string $familyCode, array $attributesMapping): void
    {
        $this->api->setToken($this->getToken());
        $mapping = $this->normalizer->normalize($attributesMapping);

        $this->api->update($familyCode, $mapping);
    }

    /**
     * @param string $status
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    private function mapAttributeMappingStatus(string $status): int
    {
        $mapping = [
            AttributeMapping::STATUS_PENDING => DomainAttributeMapping::ATTRIBUTE_PENDING,
            AttributeMapping::STATUS_INACTIVE => DomainAttributeMapping::ATTRIBUTE_UNMAPPED,
            AttributeMapping::STATUS_ACTIVE => DomainAttributeMapping::ATTRIBUTE_MAPPED,
        ];

        if (!array_key_exists($status, $mapping)) {
            throw new \InvalidArgumentException(sprintf('Unknown mapping attribute status "%s"', $status));
        }

        return $mapping[$status];
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
