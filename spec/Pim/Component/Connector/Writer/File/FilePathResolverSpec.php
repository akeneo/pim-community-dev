<?php

namespace spec\Pim\Component\Connector\Writer\File;

use PhpSpec\ObjectBehavior;

class FilePathResolverSpec extends ObjectBehavior
{
    function it_is_file_path_resolver()
    {
        $this->shouldImplement('Pim\Component\Connector\Writer\File\FilePathResolverInterface');
    }

    function it_resolves_a_file_path()
    {
        $this->resolve(
            '/path/to/my/file_%token1%_%token2%',
            [
                'parameters' => [
                    '%token1%' => 'value1',
                    '%token2%' => 'value2'
                ]
            ]
        )->shouldReturn('/path/to/my/file_value1_value2');
    }

    function it_requires_a_parameters_options()
    {
        $this
            ->shouldThrow('Symfony\Component\OptionsResolver\Exception\MissingOptionsException')
            ->during('resolve', ['/path/to/my/file_%token%']);
    }
}
