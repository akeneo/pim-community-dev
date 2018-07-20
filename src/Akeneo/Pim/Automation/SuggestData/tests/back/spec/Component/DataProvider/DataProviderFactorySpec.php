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

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\DataProvider;

use Akeneo\Pim\Automation\SuggestData\Component\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Component\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Component\DataProvider\DataProviderRegistry;
use PhpSpec\ObjectBehavior;

class DataProviderFactorySpec extends ObjectBehavior
{
    function let(DataProviderRegistry $registry)
    {
        $this->beConstructedWith($registry, 'alias');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DataProviderFactory::class);
    }

    function it_creates_a_data_provider($registry, DataProviderInterface $dataProvider)
    {
        $registry->getDataProvider('alias')->willReturn($dataProvider);

        $this->create()->shouldReturn($dataProvider);
    }
}
