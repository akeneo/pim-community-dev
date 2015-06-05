<?php

namespace spec\Akeneo\Component\FileStorage;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PathGeneratorSpec extends ObjectBehavior
{
    public function it_generates_the_path_info_of_a_file(\SplFileInfo $file)
    {
        $file->getFilename()->willReturn('[test]un FICHIER plutôt sympa23.txt');

        $pathInfo = $this->generate($file);
        $pathInfo->shouldBeValidPathInfo('_test_un_FICHIER_plut__t_sympa23.txt');
    }

    function it_cuts_the_filename_if_it_is_too_long(\SplFileInfo $file)
    {
        $file->getFilename()->willReturn('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.pdf');
        $file->getExtension()->willReturn('pdf');

        $pathInfo = $this->generate($file);
        $pathInfo->shouldBeValidPathInfo('Lorem_ipsum_dolor_sit_amet__consectetur_adipiscing_elit__sed_do_eiusmod_tempor_incididunt_ut_la.pdf');
    }

    public function getMatchers()
    {
        return [
            'beValidPathInfo' => function ($subject, $expectedFilename) {
                $guid     = $subject['guid'];
                $filename = $subject['file_name'];
                $path     = $subject['path'];
                $pathname = $subject['path_name'];

                return 40 === strlen($guid) &&
                    $guid . '_' . $expectedFilename === $filename &&
                    $guid[0] . '/' . $guid[1] . '/' . $guid[2] . '/' . $guid[3] . '/' === $path &&
                    $path . $guid . '_' . $expectedFilename === $pathname;
            },
        ];
    }
}
