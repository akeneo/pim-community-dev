<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater\MediaFile;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater\MediaFile\MediaTypeUpdater;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\MediaFile\EditMediaTypeCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class MediaTypeUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MediaTypeUpdater::class);
    }

    function it_only_supports_media_type_edits_on_media_file_attribute(
        TextAttribute $otherAttribute,
        MediaFileAttribute $mediaFileAttribute
    )
    {
        $editMediaTypeCommand = new EditMediaTypeCommand('dummy_identifier', MediaType::IMAGE);
        $otherCommand = new EditLabelsCommand('dummy', []);

        $this->supports($mediaFileAttribute, $editMediaTypeCommand)->shouldReturn(true);
        $this->supports($mediaFileAttribute, $otherCommand)->shouldReturn(false);
        $this->supports($otherAttribute, $editMediaTypeCommand)->shouldReturn(false);
    }

    function it_updates_the_media_type_of_media_file_attribute(MediaFileAttribute $mediaFileAttribute)
    {
        $editMediaTypeCommand = new EditMediaTypeCommand('dummy_identifier', MediaType::IMAGE);

        $mediaFileAttribute->setMediaType(MediaType::fromString(MediaType::IMAGE))
            ->shouldBeCalled();
        $this->__invoke($mediaFileAttribute, $editMediaTypeCommand);
    }

    function it_throws_when_the_command_is_not_valid(MediaFileAttribute $mediaFileAttribute)
    {
        $otherCommand = new EditLabelsCommand('dummy', []);

        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [
            $mediaFileAttribute,
            $otherCommand
        ]);
    }
}
