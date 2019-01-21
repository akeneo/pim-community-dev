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

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributesMapping as DomainAttributesMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AttributesMapping\AttributesMappingWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\AttributesMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter\AttributesMappingProvider;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AttributesMappingProviderSpec extends ObjectBehavior
{
    public function let(
        AttributesMappingWebService $api,
        ConfigurationRepositoryInterface $configurationRepo
    ): void {
        $this->beConstructedWith($api, $configurationRepo);
        $configuration = new Configuration();
        $configuration->setToken(new Token('valid-token'));
        $configurationRepo->find()->willReturn($configuration);
    }

    public function it_is_an_attributes_mapping_provider(): void
    {
        $this->shouldHaveType(AttributesMappingProvider::class);
        $this->shouldImplement(AttributesMappingProviderInterface::class);
    }

    public function it_gets_attributes_mapping($api): void
    {
        $api->setToken('valid-token')->shouldBeCalled();
        $response = new AttributesMapping([
            [
                'from' => [
                    'id' => 'product_weight',
                    'label' => [
                        'en_us' => 'Product Weight',
                    ],
                    'type' => 'metric',
                ],
                'to' => null,
                'summary' => ['23kg',  '12kg'],
                'status' => 'pending',
            ],
            [
                'from' => [
                    'id' => 'color',
                    'type' => 'multiselect',
                ],
                'to' => ['id' => 'color'],
                'status' => 'pending',
                'summary' => ['blue',  'red'],
            ],
        ]);
        $api->fetchByFamily('camcorders')->willReturn($response);

        $attributesMappingResponse = $this->getAttributesMapping('camcorders');
        $attributesMappingResponse->shouldHaveCount(2);
    }

    public function it_updates_attributes_mapping($api): void
    {
        $familyCode = 'foobar';
        $attributesMapping = new DomainAttributesMapping($familyCode);

        $api->setToken('valid-token')->shouldBeCalled();
        $api->save($familyCode, Argument::type('array'))->shouldBeCalled();

        $this->saveAttributesMapping($familyCode, $attributesMapping);
    }
}
