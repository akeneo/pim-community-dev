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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\IdentifiersMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\IdentifiersMapping\IdentifiersMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter\IdentifiersMappingProvider;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class IdentifiersMappingProviderSpec extends ObjectBehavior
{
    public function let(
        IdentifiersMappingApiInterface $api,
        IdentifiersMappingNormalizer $normalizer,
        ConfigurationRepositoryInterface $configurationRepo
    ): void {
        $this->beConstructedWith($api, $normalizer, $configurationRepo);

        $configuration = new Configuration();
        $configuration->setToken(new Token('valid-token'));
        $configurationRepo->find()->willReturn($configuration);
        $api->setToken(Argument::any())->shouldBeCalled();
    }

    public function it_is_an_identifier_mapping_provider(): void
    {
        $this->shouldHaveType(IdentifiersMappingProvider::class);
        $this->shouldImplement(IdentifiersMappingProviderInterface::class);
    }

    public function it_updates_the_identifiers_mapping($api, $normalizer, IdentifiersMapping $mapping): void
    {
        $normalizedMapping = ['foo' => 'bar'];

        $normalizer->normalize($mapping)->shouldBeCalled()->willReturn($normalizedMapping);
        $api->update($normalizedMapping)->shouldBeCalled();

        $this->updateIdentifiersMapping($mapping);
    }
}
