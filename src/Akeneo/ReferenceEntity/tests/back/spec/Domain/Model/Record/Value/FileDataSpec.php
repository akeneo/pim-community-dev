<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Record\Value;

use Akeneo\ReferenceEntity\Domain\Model\Record\Value\FileData;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class FileDataSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('createFromNormalize', [
            [
                'filePath' => 'f/r/z/a/oihdaozijdoiaaodoaoiaidjoaihd',
                'originalFilename' => 'file.ext',
                'size' => 1024,
                'mimeType' => 'image/ext',
                'extension' => 'ext'
            ]
        ]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FileData::class);
    }

    public function it_can_be_constructed_through_normalized_data()
    {
        $this->beConstructedThrough('createFromNormalize', [
            [
                'filePath' => 'f/r/z/a/oihdaozijdoiaaodoaoiaidjoaihd',
                'originalFilename' => 'file.ext',
                'size' => 1024,
                'mimeType' => 'image/ext',
                'extension' => 'ext'
            ]
        ]);
        $this->shouldBeAnInstanceOf(FileData::class);
    }

    public function it_cannot_be_constructed_with_something_else_than_a_normalized_array()
    {
        $this->beConstructedThrough('createFromNormalize', ['file.ext']);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_a_missing_key()
    {
        $this->beConstructedThrough('createFromNormalize', [
            ['file_key' => 'f/r/z/a/oihdaozijdoiaaodoaoiaidjoaihd']
        ]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();


        $this->beConstructedThrough('createFromNormalize', [
            ['original_filename' => 'file.ext']
        ]);
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_can_be_constructed_from_a_file_info(FileInfoInterface $fileInfo)
    {
        $fileInfo->getKey()->willReturn('A9E87F76A6F87A87E68F768A7E6F');
        $fileInfo->getOriginalFilename()->willReturn('file.ext');

        $this->beConstructedThrough('createFromFileinfo', [$fileInfo]);
        $this->shouldBeAnInstanceOf(FileData::class);
    }

    public function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(
            [
                'filePath' => 'f/r/z/a/oihdaozijdoiaaodoaoiaidjoaihd',
                'originalFilename' => 'file.ext',
                'size' => 1024,
                'mimeType' => 'image/ext',
                'extension' => 'ext'
            ]
        );
    }
}
