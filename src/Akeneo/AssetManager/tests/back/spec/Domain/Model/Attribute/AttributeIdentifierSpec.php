<?php

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use PhpSpec\ObjectBehavior;

class AttributeIdentifierSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('create', ['an_asset_family_identifier', 'description', 'test']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AttributeIdentifier::class);
    }

    public function it_cannot_be_constructed_with_empty_strings()
    {
        $this->beConstructedThrough('create', ['', '', '']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();

        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_should_contain_only_letters_numbers_underscores_and_dashes()
    {
        $this->beConstructedThrough('create', ['badId!', 'valid_code', 'valid_fingerprint']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();

        $this->beConstructedThrough('create', ['valid_identifier', 'badCode!', 'valid_fingerprint']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();

        $this->beConstructedThrough('create', ['valid_identifier', 'valid_code', 'badFingerprint!']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();

        $this->beConstructedThrough('fromString', ['invalid_identifier!']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_an_empty_string()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('create', ['', 'description', 'valid_fingerprint']);
        $this->shouldThrow('\InvalidArgumentException')->during('create', ['asset_family_identifier', '', 'valid_fingerprint']);
        $this->shouldThrow('\InvalidArgumentException')->during('create', ['asset_family_identifier', 'description', '']);
    }

    public function it_truncates_identifier_and_code_to_be_maximum_20_characters_long()
    {
        $this->beConstructedThrough('create', [
            'a_very_long_asset_family_identifier',
            'a_very_long_attribute_code',
            'fingerprint',
        ]);

        $this->normalize()->shouldReturn('a_very_long_attribut_a_very_long_asset_fa_fingerprint');
    }

    public function it_is_possible_to_compare_it()
    {
        $sameIdentifier = AttributeIdentifier::create(
            'an_asset_family_identifier',
            'description',
            'test'
        );
        $differentIdentifier = AttributeIdentifier::create(
            'an_other_asset_family_identifier',
            'title',
            'test'
        );
        $this->equals($sameIdentifier)->shouldReturn(true);
        $this->equals($differentIdentifier)->shouldReturn(false);
    }

    public function it_can_be_constructed_from_a_string()
    {
        $identifier = 'asset_family_identifier_description_test';
        $this->beConstructedThrough('fromString', [$identifier]);
        $this->stringValue()->shouldBe($identifier);
        $this->__toString()->shouldBe($identifier);
    }

    public function it_cannot_be_constructed_with_a_string_too_long()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromString', [str_repeat('a', 256)]);
    }

    public function it_cannot_be_constructed_from_a_string_with_invalid_characters()
    {
        $this->beConstructedThrough('fromString', ['badId!_valid_code_valid_fingerprint']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_normalize_itself()
    {
        $this->normalize()->shouldReturn('description_an_asset_family_iden_test');
    }
}
