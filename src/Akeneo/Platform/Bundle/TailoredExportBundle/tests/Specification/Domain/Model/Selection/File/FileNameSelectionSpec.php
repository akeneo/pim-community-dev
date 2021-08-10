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

namespace Specification\Akeneo\Platform\TailoredExport\Domain\Model\Selection\File;

use Akeneo\Platform\TailoredExport\Domain\Model\Selection\File\FileNameSelection;
use PhpSpec\ObjectBehavior;

class FileNameSelectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('foo_attribute_code');
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(FileNameSelection::class);
    }

    public function it_returns_the_attribute_code()
    {
        $this->getAttributeCode()->shouldReturn('foo_attribute_code');
    }
}
