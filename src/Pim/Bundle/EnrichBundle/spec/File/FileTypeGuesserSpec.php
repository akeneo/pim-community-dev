<?php

namespace spec\Pim\Bundle\EnrichBundle\File;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\File\FileTypes;

class FileTypeGuesserSpec extends ObjectBehavior
{
    function it_implements_file_type_guesser_interface()
    {
        $this->shouldImplement('Pim\Bundle\EnrichBundle\File\FileTypeGuesserInterface');
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
