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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AttributeOptionsMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FranklinAttributeId;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\OptionsMapping\OptionsMappingInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\OptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter\AttributeOptionsMappingProvider;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributeOptionsMappingProviderSpec extends ObjectBehavior
{
    public function let(OptionsMappingInterface $api, ConfigurationRepositoryInterface $configurationRepo): void
    {
        $configuration = new Configuration();
        $configuration->setToken(new Token('valid-token'));
        $configurationRepo->find()->willReturn($configuration);
        $api->setToken(Argument::any())->shouldBeCalled();

        $this->beConstructedWith($api, $configurationRepo);
    }

    public function it_is_an_attribute_options_mapping_provider(): void
    {
        $this->shouldHaveType(AttributeOptionsMappingProvider::class);
        $this->shouldImplement(AttributeOptionsMappingProviderInterface::class);
    }

    public function it_retrieves_attribute_options_mapping($api): void
    {
        $fakeDirectory = realpath(__DIR__ . '/../../../../Resources/fake/franklin-api/attribute-options-mapping');
        $filename = 'get_family_router_attribute_color.json';
        $mappingData = json_decode(file_get_contents(sprintf('%s/%s', $fakeDirectory, $filename)), true);

        $strFamilyCode = 'family_code';
        $strFranklinAttrId = 'franklin_attr_id';
        $api
            ->fetchByFamilyAndAttribute($strFamilyCode, $strFranklinAttrId)
            ->willReturn(new OptionsMapping($mappingData));

        $this
            ->getAttributeOptionsMapping(new FamilyCode($strFamilyCode), new FranklinAttributeId($strFranklinAttrId))
            ->shouldReturnAnInstanceOf(AttributeOptionsMapping::class);
    }
}
