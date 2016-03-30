<?php

namespace spec\Pim\Component\Connector\Reader\File;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FileIteratorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('csv');
    }

    function it_gets_current_row()
    {
        $filePath = __DIR__ . '/../../../../../../features/Context/fixtures/with_media.csv';
        $this->setFilePath($filePath);

        $this->rewind();
        $this->next();
        $this->current()->shouldReturn(
            [
                'sku;name;view;manual-fr_FR' => 'SKU-001;door;sku-001.jpg;sku-001.txt'
            ]
        );
    }

    function it_gets_current_row_from_an_archive()
    {
        $filePath = __DIR__ . '/../../../../../../features/Context/fixtures/caterpillar_import.zip';
        $this->setFilePath($filePath);

        $this->rewind();
        $this->next();
        $this->current()->shouldReturn(
            [
                'sku;family;groups;categories;name-en_US;description-en_US-mobile;side_view;color;size' =>
                    'CAT-001;boots;caterpillar_boots;winter_collection;Caterpillar 1;Model 1 boots;cat_001.png;black;37'
            ]
        );
    }

    function it_returns_null_at_the_end_of_file()
    {
        $filePath = __DIR__ . '/../../../../../../features/Context/fixtures/with_media.csv';
        $this->setFilePath($filePath);

        $this->rewind();
        $this->next();
        $this->next();
        $this->current()->shouldReturn(null);
    }

    function it_returns_directory_from_filepath()
    {
        $filePath = __DIR__ . '/../../../../../../features/Context/fixtures/with_media.csv';
        $this->setFilePath($filePath);
        $this->rewind();

        $this->getDirectoryPath()->shouldReturn(__DIR__ . '/../../../../../../features/Context/fixtures');
    }

    function it_returns_directory_created_for_archive()
    {
        $filePath = __DIR__ . '/../../../../../../features/Context/fixtures/caterpillar_import.zip';
        $this->setFilePath($filePath);
        $this->rewind();

        $this->getDirectoryPath()->shouldReturn(__DIR__ . '/../../../../../../features/Context/fixtures/caterpillar_import');
    }

    function it_returns_key()
    {
        $filePath = __DIR__ . '/../../../../../../features/Context/fixtures/with_media.csv';
        $this->setFilePath($filePath);

        $this->rewind();
        $this->next();
        $this->key()->shouldReturn(2);
    }

    function it_returns_true_if_current_position_is_valid()
    {
        $filePath = __DIR__ . '/../../../../../../features/Context/fixtures/with_media.csv';
        $this->setFilePath($filePath);

        $this->rewind();
        $this->next();
        $this->valid()->shouldReturn(true);
    }

    function it_returns_false_if_current_position_is_not_valid()
    {
        $filePath = __DIR__ . '/../../../../../../features/Context/fixtures/with_media.csv';
        $this->setFilePath($filePath);

        $this->rewind();
        $this->next();
        $this->next();
        $this->valid()->shouldReturn(false);
    }
}
