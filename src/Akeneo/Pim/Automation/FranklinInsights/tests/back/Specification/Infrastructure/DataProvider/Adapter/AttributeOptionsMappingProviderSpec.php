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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AttributeOptionsMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\OptionsMapping\OptionsMappingWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\FakeClient;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\OptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter\AttributeOptionsMappingProvider;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer\AttributeOptionsMappingNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeOptionsMappingProviderSpec extends ObjectBehavior
{
    public function let(
        OptionsMappingWebService $api,
        ConfigurationRepositoryInterface $configurationRepo,
        AttributeOptionsMappingNormalizer $attributeOptionsMappingNormalizer
    ): void {
        $configuration = new Configuration();
        $configuration->setToken(new Token('valid-token'));
        $configurationRepo->find()->willReturn($configuration);

        $this->beConstructedWith($api, $configurationRepo, $attributeOptionsMappingNormalizer);
    }

    public function it_is_an_attribute_options_mapping_provider(): void
    {
        $this->shouldHaveType(AttributeOptionsMappingProvider::class);
        $this->shouldImplement(AttributeOptionsMappingProviderInterface::class);
    }

    public function it_retrieves_attribute_options_mapping($api): void
    {
        $api->setToken(Argument::type('string'))->shouldBeCalled();
        $filepath = realpath(FakeClient::FAKE_PATH) . '/mapping/router/attributes/color/options.json';
        $mappingData = json_decode(file_get_contents($filepath), true);

        $strFamilyCode = 'family_code';
        $strFranklinAttrId = 'franklin_attr_id';
        $api
            ->fetchByFamilyAndAttribute($strFamilyCode, $strFranklinAttrId)
            ->willReturn(new OptionsMapping($mappingData['mapping']));

        $this
            ->getAttributeOptionsMapping(new FamilyCode($strFamilyCode), new FranklinAttributeId($strFranklinAttrId))
            ->shouldReturnAnInstanceOf(AttributeOptionsMapping::class);
    }
}
