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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping as DomainAttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AttributesMapping\AttributesMappingWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer\AttributesMappingNormalizer;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AttributesMappingProvider extends AbstractProvider implements AttributesMappingProviderInterface
{
    /** @var AttributesMappingWebService */
    private $api;

    /**
     * @param AttributesMappingWebService $api
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(
        AttributesMappingWebService $api,
        ConfigurationRepositoryInterface $configurationRepository
    ) {
        parent::__construct($configurationRepository);

        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributesMapping(string $familyCode): AttributesMappingResponse
    {
        $this->api->setToken($this->getToken());

        try {
            $apiResponse = $this->api->fetchByFamily($familyCode);
        } catch (FranklinServerException $e) {
            throw DataProviderException::serverIsDown($e);
        } catch (InvalidTokenException $e) {
            throw DataProviderException::authenticationError($e);
        } catch (BadRequestException $e) {
            throw DataProviderException::badRequestError($e);
        }

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
    public function saveAttributesMapping(string $familyCode, array $attributesMapping): void
    {
        $this->api->setToken($this->getToken());
        $normalizer = new AttributesMappingNormalizer();
        $mapping = $normalizer->normalize($attributesMapping);

        try {
            $this->api->save($familyCode, $mapping);
        } catch (FranklinServerException $e) {
            throw DataProviderException::serverIsDown($e);
        } catch (InvalidTokenException $e) {
            throw DataProviderException::authenticationError($e);
        } catch (BadRequestException $e) {
            throw DataProviderException::badRequestError($e);
        }
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
}
