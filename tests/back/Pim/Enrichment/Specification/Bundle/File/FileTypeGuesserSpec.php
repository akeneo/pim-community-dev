<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\File;

use Akeneo\Pim\Enrichment\Bundle\File\FileTypeGuesserInterface;
use Akeneo\Pim\Enrichment\Bundle\File\FileTypes;
use PhpSpec\ObjectBehavior;

class FileTypeGuesserSpec extends ObjectBehavior
{
    function it_implements_file_type_guesser_interface()
    {
        $this->shouldImplement(FileTypeGuesserInterface::class);
    }

    function it_guesses_file_types_from_mime_types()
    {
        $this->guess('text/plain')->shouldReturn(FileTypes::DOCUMENT);
        $this->guess('image/jpeg')->shouldReturn(FileTypes::IMAGE);
        $this->guess('video/mpeg')->shouldReturn(FileTypes::VIDEO);
        $this->guess('unknown/mime-type')->shouldReturn(FileTypes::MISC);
    }

    function it_can_guess_extra_file_type_from_mime_types()
    {
        $this->beConstructedWith(['pim_enrich_file_audio' => ['audio/*']]);

        $this->guess('audio/mpeg')->shouldReturn('pim_enrich_file_audio');
        $this->guess('text/plain')->shouldReturn(FileTypes::DOCUMENT);
        $this->guess('image/jpeg')->shouldReturn(FileTypes::IMAGE);
        $this->guess('video/mpeg')->shouldReturn(FileTypes::VIDEO);
    }
}
