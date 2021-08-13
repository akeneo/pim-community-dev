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

namespace Specification\Akeneo\Platform\TailoredExport\Application\ExtractMedia;

use Akeneo\Platform\TailoredExport\Application\ExtractMedia\ExtractedMedia;
use PhpSpec\ObjectBehavior;

class ExtractedMediaSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('a_filekey', 'catalog', 'files/folder_path/name');
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(ExtractedMedia::class);
    }

    public function it_returns_the_key()
    {
        $this->getKey()->shouldReturn('a_filekey');
    }

    public function it_returns_the_storage()
    {
        $this->getStorage()->shouldReturn('catalog');
    }

    public function it_returns_the_path()
    {
        $this->getPath()->shouldReturn('files/folder_path/name');
    }
}
