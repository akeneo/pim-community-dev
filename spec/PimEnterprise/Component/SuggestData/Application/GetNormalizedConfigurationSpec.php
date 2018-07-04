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

namespace spec\PimEnterprise\Component\SuggestData\Application;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\SuggestData\Model\Configuration;
use PimEnterprise\Component\SuggestData\Application\GetNormalizedConfiguration;
use PimEnterprise\Component\SuggestData\Repository\ConfigurationRepositoryInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetNormalizedConfigurationSpec extends ObjectBehavior
{
    function let(ConfigurationRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetNormalizedConfiguration::class);
    }

    function it_queries_a_normalized_configuration($repository)
    {
        $configuration = new Configuration('foobar', ['foo' => 'bar']);
        $repository->findOneByCode('foobar')->willReturn($configuration);

        $this->fromCode('foobar')->shouldReturn([
            'code' => 'foobar',
            'values' => ['foo' => 'bar'],
        ]);
    }

    function it_returns_an_empty_array_if_configuration_does_not_exist($repository)
    {
        $repository->findOneByCode('foobar')->willReturn(null);

        $this->fromCode('foobar')->shouldReturn([]);
    }
}
