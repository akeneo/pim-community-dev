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

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\IdentifiersMappingProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Configuration;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\IdentifiersMapping\IdentifiersMappingWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter\IdentifiersMappingProvider;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class IdentifiersMappingProviderSpec extends ObjectBehavior
{
    public function let(
        ConfigurationRepositoryInterface $configurationRepo,
        IdentifiersMappingWebService $api,
        IdentifiersMappingNormalizer $normalizer
    ): void {
        $this->beConstructedWith($configurationRepo, $api, $normalizer);

        $normalizer->normalize(Argument::any())->willReturn([]);
        $configuration = new Configuration();
        $configuration->setToken(new Token('valid-token'));
        $configurationRepo->find()->willReturn($configuration);
    }

    public function it_is_an_identifier_mapping_provider(): void
    {
        $this->shouldHaveType(IdentifiersMappingProvider::class);
        $this->shouldImplement(IdentifiersMappingProviderInterface::class);
    }

    public function it_updates_the_identifiers_mapping($api, IdentifiersMapping $mapping, $normalizer): void
    {
        $api->setToken(Argument::type('string'))->shouldBeCalled();
        $mapping->getMapping()->willReturn([]);

        $api->save(Argument::any())->shouldBeCalled();

        $this->saveIdentifiersMapping($mapping);
    }

    public function it_throws_an_exception_if_ask_franklin_was_down(
        $api,
        IdentifiersMapping $mapping
    ): void {
        $api->setToken(Argument::type('string'))->shouldBeCalled();
        $mapping->getMapping()->willReturn([]);

        $catchedException = new FranklinServerException();
        $api->save(Argument::any())->willThrow($catchedException);

        $thrownException = DataProviderException::serverIsDown($catchedException);
        $this->shouldThrow($thrownException)->during('saveIdentifiersMapping', [$mapping]);
    }
}
