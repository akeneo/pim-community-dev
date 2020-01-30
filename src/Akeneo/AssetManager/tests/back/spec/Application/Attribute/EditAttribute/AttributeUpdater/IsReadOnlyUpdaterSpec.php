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

namespace spec\Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater;

use Akeneo\AssetManager\Application\Attribute\EditAttribute\AttributeUpdater\IsReadOnlyUpdater;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditIsReadOnlyCommand;
use Akeneo\AssetManager\Application\Attribute\EditAttribute\CommandFactory\EditLabelsCommand;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class IsReadOnlyUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(IsReadOnlyUpdater::class);
    }

    function it_only_supports_edit_read_only_command_for_all_attributes(
        TextAttribute $textAttribute,
        MediaFileAttribute $mediaFileAttribute
    ) {
        $labelEditCommand = new EditLabelsCommand('name', []);
        $isReadOnlyCommand = new EditIsReadOnlyCommand('name', false);

        $this->supports($textAttribute, $isReadOnlyCommand)->shouldReturn(true);
        $this->supports($mediaFileAttribute, $isReadOnlyCommand)->shouldReturn(true);
        $this->supports($textAttribute, $labelEditCommand)->shouldReturn(false);
    }

    function it_edits_the_read_only_property_of_an_attribute(TextAttribute $textAttribute)
    {
        $isReadOnlyCommand = new EditIsReadOnlyCommand('name', false);
        $textAttribute->setIsReadOnly(AttributeIsReadOnly::fromBoolean(false))->shouldBeCalled();

        $this->__invoke($textAttribute, $isReadOnlyCommand)->shouldReturn($textAttribute);
    }

    function it_throws__an_exception_if_it_cannot_update_the_attribute(TextAttribute $textAttribute)
    {
        $wrongCommand = new EditLabelsCommand('name', []);
        $this->shouldThrow(\RuntimeException::class)->during('__invoke', [$textAttribute, $wrongCommand]);
    }
}
