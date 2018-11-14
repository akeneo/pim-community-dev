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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AuthenticationProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FranklinAttributeId;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AttributesMapping\AttributesMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\IdentifiersMapping\IdentifiersMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\OptionsMapping\OptionsMappingInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\ClientException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\FakeClient;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\AttributesMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\ValueObject\OptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter\Franklin;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\AttributesMappingNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * TODO: There are lot of spec to add. Half of the class is not spec.
 */
class FranklinSpec extends ObjectBehavior
{
    public function let(
        AuthenticationProviderInterface $authenticationProvider,
        SubscriptionApiInterface $subscriptionApi,
        IdentifiersMappingApiInterface $identifiersMappingApi,
        AttributesMappingApiInterface $attributesMappingApi,
        OptionsMappingInterface $attributeOptionsMappingApi,
        IdentifiersMappingNormalizer $identifiersMappingNormalizer,
        AttributesMappingNormalizer $attributesMappingNormalizer,
        ConfigurationRepositoryInterface $configurationRepository,
        SubscriptionProviderInterface $productSubscription
    ): void {
        $configuration = new Configuration();
        $configuration->setToken(new Token('valid-token'));
        $configurationRepository->find()->willReturn($configuration);
        $this->beConstructedWith(
            $authenticationProvider,
            $subscriptionApi,
            $identifiersMappingApi,
            $attributesMappingApi,
            $attributeOptionsMappingApi,
            $identifiersMappingNormalizer,
            $attributesMappingNormalizer,
            $configurationRepository,
            $productSubscription
        );
    }

    public function it_is_franklin_adapter(): void
    {
        $this->shouldHaveType(Franklin::class);
        $this->shouldImplement(DataProviderInterface::class);
    }

    public function it_updates_the_identifiers_mapping(
        IdentifiersMappingApiInterface $identifiersMappingApi,
        IdentifiersMappingNormalizer $identifiersMappingNormalizer,
        IdentifiersMapping $mapping
    ): void {
        $normalizedMapping = ['foo' => 'bar'];

        $identifiersMappingNormalizer->normalize($mapping)->shouldBeCalled()->willReturn($normalizedMapping);
        $identifiersMappingApi->setToken(Argument::type('string'))->shouldBeCalled();
        $identifiersMappingApi->update($normalizedMapping)->shouldBeCalled();

        $this->updateIdentifiersMapping($mapping);
    }

    public function it_unsubscribes_a_subscription_id_from_franklin($subscriptionApi): void
    {
        $subscriptionApi->setToken(Argument::type('string'))->shouldBeCalled();
        $subscriptionApi->unsubscribeProduct('foo-bar')->shouldBeCalled();

        $this->unsubscribe('foo-bar')->shouldReturn(null);
    }

    public function it_throws_a_product_subscription_exception_on_client_exception($subscriptionApi): void
    {
        $subscriptionApi->setToken(Argument::type('string'))->shouldBeCalled();

        $clientException = new ClientException('exception-message');
        $subscriptionApi->unsubscribeProduct('foo-bar')->willThrow($clientException);

        $this
            ->shouldThrow(new ProductSubscriptionException('exception-message'))
            ->during(
                'unsubscribe',
                ['foo-bar']
            );
    }

    public function it_gets_attributes_mapping($attributesMappingApi): void
    {
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
        $attributesMappingApi->fetchByFamily('camcorders')->willReturn($response);
        $attributesMappingApi->setToken(Argument::type('string'))->shouldBeCalled();

        $attributesMappingResponse = $this->getAttributesMapping('camcorders');
        $attributesMappingResponse->shouldHaveCount(2);
    }

    public function it_updates_attributes_mapping($attributesMappingApi, $attributesMappingNormalizer): void
    {
        $familyCode = 'foobar';
        $attributesMapping = ['foo' => 'bar'];
        $normalizedMapping = ['bar' => 'foo'];

        $attributesMappingNormalizer->normalize($attributesMapping)->willReturn($normalizedMapping);
        $attributesMappingApi->setToken(Argument::type('string'))->shouldBeCalled();
        $attributesMappingApi->update($familyCode, $normalizedMapping)->shouldBeCalled();

        $this->updateAttributesMapping($familyCode, $attributesMapping);
    }

    public function it_retrieves_attribute_options_mapping($attributeOptionsMappingApi): void
    {
        $filepath = realpath(FakeClient::FAKE_PATH) . '/mapping/router/attributes/color/options.json';
        $mappingData = json_decode(file_get_contents($filepath), true);

        $strFamilyCode = 'family_code';
        $strFranklinAttrId = 'franklin_attr_id';
        $attributeOptionsMappingApi->setToken(Argument::type('string'))->shouldBeCalled();
        $attributeOptionsMappingApi
            ->fetchByFamilyAndAttribute($strFamilyCode, $strFranklinAttrId)
            ->willReturn(new OptionsMapping($mappingData));

        $this
            ->getAttributeOptionsMapping(new FamilyCode($strFamilyCode), new FranklinAttributeId($strFranklinAttrId))
            ->shouldReturnAnInstanceOf(AttributeOptionsMapping::class);
    }
}
