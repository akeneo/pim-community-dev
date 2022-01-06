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

namespace Specification\Akeneo\Platform\TailoredExport\Application\Common\SourceValue;

use Akeneo\Platform\TailoredExport\Application\Common\SourceValue\FileValue;
use PhpSpec\ObjectBehavior;

class FileValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            'an_entity_identifier',
            'catalog',
            'a_filekey',
            'an_original_filename',
            null,
            null
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(FileValue::class);
    }

    public function it_returns_the_entity_identifier()
    {
        $this->getEntityIdentifier()->shouldReturn('an_entity_identifier');
    }

    public function it_returns_the_storage()
    {
        $this->getStorage()->shouldReturn('catalog');
    }

    public function it_returns_the_key()
    {
        $this->getKey()->shouldReturn('a_filekey');
    }

    public function it_returns_the_original_filename()
    {
        $this->getOriginalFilename()->shouldReturn('an_original_filename');
    }

    public function it_returns_the_channel_reference()
    {
        $this->getChannelReference()->shouldReturn(null);
    }

    public function it_returns_the_locale_reference()
    {
        $this->getLocaleReference()->shouldReturn(null);
    }
}
