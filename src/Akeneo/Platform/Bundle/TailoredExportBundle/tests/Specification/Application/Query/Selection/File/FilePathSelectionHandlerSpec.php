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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Query\Selection\File;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\Boolean\BooleanSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\File\FilePathSelection;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\BooleanValue;
use Akeneo\Platform\TailoredExport\Domain\Model\SourceValue\FileValue;
use PhpSpec\ObjectBehavior;

class FilePathSelectionHandlerSpec extends ObjectBehavior
{
    public function it_applies_the_selection()
    {
        $selection = new FilePathSelection('an_attribute_code');
        $value = new FileValue(
            'identifier',
            'catalog',
            'a_filekey',
            'original_filename',
            null,
            null
        );

        $this->applySelection($selection, $value)
            ->shouldReturn('files/identifier/an_attribute_code/original_filename');
    }

    public function it_applies_the_selection_with_all_value()
    {
        $selection = new FilePathSelection('an_attribute_code');
        $value = new FileValue(
            'identifier',
            'catalog',
            'a_filekey',
            'original_filename',
            'ecommerce',
            'fr_FR'
        );

        $this->applySelection($selection, $value)
            ->shouldReturn('files/identifier/an_attribute_code/fr_FR/ecommerce/original_filename');
    }

    public function it_does_not_apply_selection_on_not_supported_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this
            ->shouldThrow(new \InvalidArgumentException('Cannot apply File selection on this entity'))
            ->during('applySelection', [$notSupportedSelection, $notSupportedValue]);
    }

    public function it_supports_file_path_selection_with_file_value()
    {
        $selection = new FilePathSelection('an_attribute_code');
        $value = new FileValue(
            'identifier',
            'catalog',
            'a_filekey',
            'original_filename',
            null,
            null
        );

        $this->supports($selection, $value)->shouldReturn(true);
    }

    public function it_does_not_support_other_selections_and_values()
    {
        $notSupportedSelection = new BooleanSelection();
        $notSupportedValue = new BooleanValue(true);

        $this->supports($notSupportedSelection, $notSupportedValue)->shouldReturn(false);
    }
}
