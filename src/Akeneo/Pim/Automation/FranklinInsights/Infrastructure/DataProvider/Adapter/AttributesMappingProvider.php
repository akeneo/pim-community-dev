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
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\AttributeMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMapping as DomainAttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\AttributeMappingCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributesMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\InvalidTokenExceptionFactory;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
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
     * @param InvalidTokenExceptionFactory $invalidTokenExceptionFactory
     */
    public function __construct(
        ConfigurationRepositoryInterface $configurationRepository,
        InvalidTokenExceptionFactory $invalidTokenExceptionFactory,
        AttributesMappingWebService $api
    ) {
        parent::__construct($configurationRepository, $invalidTokenExceptionFactory);

        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributesMapping(FamilyCode $familyCode): AttributeMappingCollection
    {
        $this->api->setToken($this->getToken());

        try {
            $apiResponse = $this->api->fetchByFamily((string) $familyCode);
        } catch (FranklinServerException $e) {
            throw DataProviderException::serverIsDown($e);
        } catch (InvalidTokenException $e) {
            throw $this->invalidTokenExceptionFactory->create($e);
        } catch (BadRequestException $e) {
            throw DataProviderException::badRequestError($e);
        }

        $attributesMapping = new AttributeMappingCollection();
        foreach ($apiResponse as $attribute) {
            $attribute = new DomainAttributeMapping(
                $attribute->getTargetAttributeCode(),
                $attribute->getTargetAttributeLabel(),
                $attribute->getTargetAttributeType(),
                $attribute->getPimAttributeCode(),
                $attribute->getStatus(),
                $attribute->getSummary()
            );
            $attributesMapping->addAttribute($attribute);
        }

        return $attributesMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function saveAttributesMapping(FamilyCode $familyCode, AttributesMapping $attributesMapping): void
    {
        $this->api->setToken($this->getToken());
        $normalizer = new AttributesMappingNormalizer();
        $mapping = $normalizer->normalize($attributesMapping);

        try {
            $this->api->save((string) $familyCode, $mapping);
        } catch (FranklinServerException $e) {
            throw DataProviderException::serverIsDown($e);
        } catch (InvalidTokenException $e) {
            throw $this->invalidTokenExceptionFactory->create($e);
        } catch (BadRequestException $e) {
            throw DataProviderException::badRequestError($e);
        }
    }
}
