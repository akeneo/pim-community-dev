<?php
declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\Attribute\MediaLink;

use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use PhpSpec\ObjectBehavior;


/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class MediaTypeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromString', [MediaType::IMAGE]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MediaType::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('image');
    }

    function it_can_only_be_created_from_a_media_type_it_supports()
    {
        $this->beConstructedThrough('fromString', [MediaType::PDF]);
    }

    function it_throws_if_the_media_type_is_not_supported($mediaType)
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('fromString', ['unknown_media_type']);
    }

    public function it_throws_if_it_is_created_with_an_empty_value()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_if_it_created_with_0_as_media_type()
    {
        $this->beConstructedThrough('fromString', ['0']);
        $this->shouldThrow(
            new \InvalidArgumentException(
                sprintf(
                    'Expected media types are "%s", "%s" given',
                    implode(', ', MediaType::MEDIA_TYPES),
                    '0'
                )
            )
        )->duringInstantiation();
    }

    public function it_throws_if_it_is_created_with_a_non_existing_type()
    {
        $this->beConstructedThrough('fromString', ['test']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
