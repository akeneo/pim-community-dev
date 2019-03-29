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

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AttributeOptionsMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionsMapping as ReadAttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Write\AttributeOptionsMapping as WriteAttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\OptionsMapping\OptionsMappingWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Converter\AttributeOptionsMappingConverter;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer\AttributeOptionsMappingNormalizer;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AttributeOptionsMappingProvider extends AbstractProvider implements AttributeOptionsMappingProviderInterface
{
    /** @var OptionsMappingWebService */
    private $api;

    /** @var AttributeOptionsMappingNormalizer */
    private $attributeOptionsMappingNormalizer;

    /**
     * @param OptionsMappingWebService $api
     * @param ConfigurationRepositoryInterface $configurationRepository
     * @param AttributeOptionsMappingNormalizer $attributeOptionsMappingNormalizer
     */
    public function __construct(
        OptionsMappingWebService $api,
        ConfigurationRepositoryInterface $configurationRepository,
        AttributeOptionsMappingNormalizer $attributeOptionsMappingNormalizer
    ) {
        parent::__construct($configurationRepository);

        $this->api = $api;
        $this->attributeOptionsMappingNormalizer = $attributeOptionsMappingNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeOptionsMapping(
        FamilyCode $familyCode,
        FranklinAttributeId $franklinAttributeId
    ): ReadAttributeOptionsMapping {
        $this->api->setToken($this->getToken());

        try {
            $franklinOptionsMapping = $this
                ->api
                ->fetchByFamilyAndAttribute((string) $familyCode, (string) $franklinAttributeId);
        } catch (FranklinServerException $e) {
            throw DataProviderException::serverIsDown($e);
        } catch (InvalidTokenException $e) {
            throw DataProviderException::authenticationError($e);
        } catch (BadRequestException $e) {
            throw DataProviderException::badRequestError($e);
        }

        $converter = new AttributeOptionsMappingConverter();

        return $converter->clientToApplication(
            $familyCode,
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
        $normalizedMapping = $this->attributeOptionsMappingNormalizer->normalize($attributeOptionsMapping);

        try {
            $this->api->update((string) $familyCode, (string) $franklinAttributeId, $normalizedMapping);
        } catch (BadRequestException $e) {
            throw DataProviderException::badRequestError($e);
        } catch (FranklinServerException $e) {
            throw DataProviderException::serverIsDown($e);
        } catch (InvalidTokenException $e) {
            throw DataProviderException::authenticationError($e);
        }
    }
}
