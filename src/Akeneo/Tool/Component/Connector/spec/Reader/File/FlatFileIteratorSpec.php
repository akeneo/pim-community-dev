<?php

namespace spec\Akeneo\Tool\Component\Connector\Reader\File;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class FlatFileIteratorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('csv', $this->getPath() . DIRECTORY_SEPARATOR  . 'with_media.csv', [
            'reader_options' => [
                'fieldDelimiter' => ';'
            ]
        ]);
    }

    function it_throws_exception_with_invalid_filename()
    {
        $this->beConstructedWith('csv', $this->getPath() . DIRECTORY_SEPARATOR  . 'unknown_file.csv', [
            'reader_options' => [
                'fieldDelimiter' => ';'
            ]
        ]);
        $this->shouldThrow(FileNotFoundException::class)->duringInstantiation();
    }

    function it_gets_current_row()
    {
        $this->rewind();
        $this->next();
        $this->current()->shouldReturn(
            [
                'SKU-001',
                'door',
                'sku-001.jpg',
                'sku-001.txt'
            ]
        );
    }

    function it_gets_current_row_from_xlsx()
    {
        $this->beConstructedWith('xlsx', $this->getPath() . DIRECTORY_SEPARATOR  . 'product_with_carriage_return.xlsx');

        $this->rewind();
        $this->next();
        $this->current()->shouldReturn(
            [
                'SKU-001',
                'boots',
                'CROSS',
                'winter_boots',
                'Donec',
                'dictum magna.

Lorem ispum
Est'
            ]
        );
    }
    function it_gets_current_row_from_an_archive()
    {
        $this->beConstructedWith('csv', $this->getPath() . DIRECTORY_SEPARATOR  . 'caterpillar_import.zip', [
            'reader_options' => [
                'fieldDelimiter' => ';'
            ]
        ]);

        $this->rewind();
        $this->next();
        $this->current()->shouldReturn(
            [
                'CAT-001',
                'boots',
                'winter_collection',
                'Caterpillar 1',
                'Model 1 boots',
                'cat_001.png',
                'black',
                '37',
            ]
        );
    }

    function it_returns_null_at_the_end_of_file()
    {
        $this->rewind();
        $this->next();
        $this->next();
        $this->current()->shouldReturn(null);
    }

    function it_returns_directory_from_filepath()
    {
        $this->rewind();
        $this->getDirectoryPath()->shouldReturn($this->getPath());
    }

    function it_returns_directory_created_for_archive()
    {
        $this->beConstructedWith('csv', $this->getPath() . DIRECTORY_SEPARATOR  . 'caterpillar_import.zip');

        $this->rewind();
        $this->getDirectoryPath()->shouldReturn($this->getPath() . DIRECTORY_SEPARATOR  . 'caterpillar_import');
    }

    function it_returns_key()
    {
        $this->rewind();
        $this->next();
        $this->key()->shouldReturn(2);
    }

    function it_returns_true_if_current_position_is_valid()
    {
        $this->rewind();
        $this->next();
        $this->valid()->shouldReturn(true);
    }

    function it_returns_false_if_current_position_is_not_valid()
    {
        $this->rewind();
        $this->next();
        $this->next();
        $this->valid()->shouldReturn(false);
    }

    private function getPath()
    {
        return __DIR__ .
               DIRECTORY_SEPARATOR  . '..' .
               DIRECTORY_SEPARATOR  . '..' .
               DIRECTORY_SEPARATOR  . '..'.
               DIRECTORY_SEPARATOR  . '..'.
               DIRECTORY_SEPARATOR  . '..'.
               DIRECTORY_SEPARATOR  . '..' .
               DIRECTORY_SEPARATOR  . '..' .
               DIRECTORY_SEPARATOR  . '..' .
               DIRECTORY_SEPARATOR  . 'tests' .
               DIRECTORY_SEPARATOR  . 'legacy' .
               DIRECTORY_SEPARATOR  . 'features' .
               DIRECTORY_SEPARATOR  . 'Context' .
               DIRECTORY_SEPARATOR  . 'fixtures';
    }
}
