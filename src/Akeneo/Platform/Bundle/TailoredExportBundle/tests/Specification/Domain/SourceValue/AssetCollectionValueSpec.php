<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Domain\SourceValue;

use Akeneo\Platform\TailoredExport\Domain\SourceValue\AssetCollectionValue;
use PhpSpec\ObjectBehavior;

class AssetCollectionValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            ['asset_code_1', 'asset_code_2', 'asset_code_3'],
            'an_entity_id',
            'ecommerce',
            'fr_FR'
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(AssetCollectionValue::class);
    }

    public function it_throws_an_exception_if_asset_codes_are_invalid()
    {
        $this->beConstructedWith(
            ['asset_code_1', 2, 'asset_code_3'],
            'an_entity_id',
            null,
            null
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_asset_codes()
    {
        $this->getAssetCodes()->shouldReturn(['asset_code_1', 'asset_code_2', 'asset_code_3']);
    }

    public function it_returns_the_entity_identifier()
    {
        $this->getEntityIdentifier()->shouldReturn('an_entity_id');
    }

    public function it_returns_the_channel_reference()
    {
        $this->getChannelReference()->shouldReturn('ecommerce');
    }

    public function it_returns_null_when_the_channel_reference_is_null()
    {
        $this->beConstructedWith(
            ['asset_code_1', 'asset_code_2', 'asset_code_3'],
            'an_entity_id',
            null,
            null
        );
        $this->getChannelReference()->shouldReturn(null);
    }

    public function it_returns_the_locale_reference()
    {
        $this->getLocaleReference()->shouldReturn('fr_FR');
    }

    public function it_returns_null_the_channel_reference_is_null()
    {
        $this->beConstructedWith(
            ['asset_code_1', 'asset_code_2', 'asset_code_3'],
            'an_entity_id',
            null,
            null
        );
        $this->getLocaleReference()->shouldReturn(null);
    }
}
