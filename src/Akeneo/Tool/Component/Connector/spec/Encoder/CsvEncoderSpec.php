<?php

namespace spec\Akeneo\Tool\Component\Connector\Encoder;

use Akeneo\Tool\Component\Connector\Encoder\CsvEncoder;
use PhpSpec\ObjectBehavior;

class CsvEncoderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CsvEncoder::class);
    }

    function it_is_a_encoder()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Encoder\EncoderInterface');
    }

    function it_is_csv_encoder()
    {
        $this->supportsEncoding('csv')->shouldReturn(true);
        $this->supportsEncoding('json')->shouldReturn(false);
    }

    function it_encodes_data_in_csv()
    {
        $this->encode(
            [
                'code' => 'foo',
                'name' => '"bar"'
            ],
            'csv',
            [
                'delimiter' => ',',
                'enclosure' => '"',
            ]
        )->shouldReturn("foo,\"\"\"bar\"\"\"\n");

        $this->encode(
            [
                'code' => 'foo',
                'name' => '"bar"'
            ],
            'csv',
            [
                'delimiter' => null,
                'enclosure' => null,
            ]
        )->shouldReturn("foo;\"\"\"bar\"\"\"\n");

        $this->encode(
            [
                'code' => 'foo',
                'name' => '"bar"'
            ],
            'csv',
            [
                'delimiter' => ';',
                'enclosure' => '"',
            ]
        )->shouldReturn("foo;\"\"\"bar\"\"\"\n");

        $this->encode(
            [
                'code' => 'foo',
                'name' => '"bar"'
            ],
            'csv',
            [
                'delimiter' => ';',
                'enclosure' => '\'',
            ]
        )->shouldReturn("foo;\"bar\"\n");

        $this->encode(
            [
                'code' => 'foo',
                'name' => '"bar"'
            ],
            'csv',
            [
                'delimiter' => null,
                'enclosure' => '\'',
            ]
        )->shouldReturn("foo;\"bar\"\n");

        $this->encode(
            [
                'code' => 'foo',
                'name' => '"bar"'
            ],
            'csv',
            [
                'delimiter' => ',',
                'enclosure' => null,
            ]
        )->shouldReturn("foo,\"\"\"bar\"\"\"\n");

        $this->encode(
            [
                ['name' => 'foo', 'code' => 'bar'],
                ['name' => 'baz', 'code' => 'buz'],
            ],
            'csv'
        )->shouldReturn("foo;bar\nbaz;buz\n");

        $this->encode(
            [],
            'csv'
        )->shouldReturn("\n");
    }

    function it_encodes_header()
    {
        $this->encode(
            [
                ['name' => 'foo', 'code' => 'bar'],
                ['name' => 'baz', 'code' => 'buz'],
            ],
            'csv',
            [
                'withHeader' => true
            ]
        )->shouldReturn("name;code\nfoo;bar\nbaz;buz\n");

        $this->encode(
            [
                ['name' => 'foo', 'code' => 'bar'],
                ['name' => 'baz', 'code' => 'buz'],
            ],
            'csv',
            [
                'withHeader' => false
            ]
        )->shouldReturn("foo;bar\nbaz;buz\n");
    }

    function it_throws_exception_when_data_are_invalid()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('encode', [null, 'csv']);
        $this->shouldThrow('\InvalidArgumentException')->during('encode', [false, 'csv']);
        $this->shouldThrow('\InvalidArgumentException')->during('encode', [true, 'csv']);
        $this->shouldThrow('\InvalidArgumentException')->during('encode', ['foo', 'csv']);
        $this->shouldThrow('\InvalidArgumentException')->during('encode', [1, 'csv']);
    }
}
