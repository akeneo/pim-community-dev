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

namespace spec\Akeneo\AssetManager\Domain\Model\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use PhpSpec\ObjectBehavior;

class AssetIdentifierSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('create', ['an_asset_family_identifier', 'a_asset_identifier', 'fingerprint']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AssetIdentifier::class);
    }

    public function it_should_contain_only_letters_numbers_dashes_and_underscores()
    {
        $this->beConstructedThrough('fromString', ['asset_identifier!']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();

        $this->beConstructedThrough('create', ['valid_identifier', 'badId!', 'fingerprint/']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_an_empty_string()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('fromString', ['']);
    }

    public function it_cannot_be_constructed_with_a_string_too_long()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', [str_repeat('a', 256)]);
    }

    public function it_cannot_be_created_with_an_empty_asset_family_identifier()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('create', ['', 'starck', 'fingerprint']);
    }

    public function it_cannot_be_created_with_an_invalid_asset_family_identifier()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('create', ['invalid_asset_family!', 'starck', 'fingerprint']);
    }

    public function it_cannot_be_created_with_an_empty_asset_code()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('create', ['designer', '', 'fingerprint']);
    }

    public function it_cannot_be_created_with_an_invalid_asset_code()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('create', ['designer', 'invalid_asset!', 'fingerprint']);
    }

    public function it_cannot_be_created_with_an_empty_fingerprint()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('create', ['designer', 'starck', '']);
    }

    public function it_cannot_be_created_with_an_invalid_fingerprint()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('create', ['designer', 'starck', 'invalid_fingerprint!']);
    }

    public function it_is_possible_to_compare_it()
    {
        $sameIdentifier = AssetIdentifier::create(
            'an_asset_family_identifier',
            'a_asset_identifier',
            'fingerprint'
        );
        $differentIdentifier = AssetIdentifier::create(
            'an_other_asset_family_identifier',
            'other_asset_identifier',
            'other_fingerprint'
        );
        $this->equals($sameIdentifier)->shouldReturn(true);
        $this->equals($differentIdentifier)->shouldReturn(false);
    }

    public function it_normalize_itself()
    {
        $this->normalize()->shouldReturn('an_asset_family_iden_a_asset_identifier_fingerprint');
    }

    public function it_can_be_transformed_into_a_string()
    {
        $this->__toString()->shouldReturn('an_asset_family_iden_a_asset_identifier_fingerprint');
    }
}
