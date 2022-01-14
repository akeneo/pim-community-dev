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

namespace Specification\Akeneo\Platform\Syndication\Application\Common\Selection\File;

use Akeneo\Platform\Syndication\Application\Common\Selection\File\FileKeySelection;
use PhpSpec\ObjectBehavior;

class FileKeySelectionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('foo_attribute_code');
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(FileKeySelection::class);
    }

    public function it_returns_the_attribute_code()
    {
        $this->getAttributeCode()->shouldReturn('foo_attribute_code');
    }
}
