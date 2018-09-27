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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\DataProvider;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderRegistry;
use PhpSpec\ObjectBehavior;

class DataProviderFactorySpec extends ObjectBehavior
{
    public function let(DataProviderRegistry $registry): void
    {
        $this->beConstructedWith($registry, 'alias');
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(DataProviderFactory::class);
    }

    public function it_creates_a_data_provider($registry, DataProviderInterface $dataProvider): void
    {
        $registry->getDataProvider('alias')->willReturn($dataProvider);

        $this->create()->shouldReturn($dataProvider);
    }
}
