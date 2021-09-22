<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Domain\Query\Asset\Connector;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAsset;
use PhpSpec\ObjectBehavior;

class ConnectorAssetResultSpec extends ObjectBehavior
{
    function it_can_be_constructed_only_with_connector_assets()
    {
        $this->beConstructedThrough('createFromSearchAfterQuery', [
            [
                new ConnectorAsset(AssetCode::fromString('code1'), []),
                new \StdClass(),
                new ConnectorAsset(AssetCode::fromString('code2'), []),
            ],
            ['value1', 120]
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_returns_the_assets()
    {
        $asset1 = new ConnectorAsset(AssetCode::fromString('code1'), []);
        $asset2 = new ConnectorAsset(AssetCode::fromString('code2'), []);
        $this->beConstructedThrough('createFromSearchAfterQuery', [
            [$asset1, $asset2],
            ['value1', 120]
        ]);

        $this->assets()->shouldBeArray();
        $this->assets()->shouldHaveCount(2);
        $this->assets()[0]->shouldBe($asset1);
        $this->assets()[1]->shouldBe($asset2);
    }

    function it_returns_the_last_sort_value()
    {
        $this->beConstructedThrough('createFromSearchAfterQuery', [
            [],
            ['value1', 120]
        ]);

        $this->lastSortValue()->shouldBe(['value1', 120]);
    }
}
