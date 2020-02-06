<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\Filesystem\PostProcessor;

use Akeneo\AssetManager\Infrastructure\Filesystem\PostProcessor\ConvertToJPGPostProcessor;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ConvertToJPGPostProcessorTest extends KernelTestCase
{
    private const FILENAME = __DIR__ . '/../../../Common/TestFixtures/lardon.png';

    /** @test */
    public function it_convert_a_png_image_to_jpg_image()
    {
        $binary = new Binary(file_get_contents(static::FILENAME), 'image/png', 'png');

        $jpgBinary = $this->getConvertToJPGPostProcessor()->process($binary, ['quality' => 70]);
        $this->assertEquals('image/jpeg', $jpgBinary->getMimeType());
        $this->assertEquals('jpg', $jpgBinary->getFormat());
        $this->assertLessThan(strlen($binary->getContent()), strlen($jpgBinary->getContent()));
    }

    /** @test */
    public function it_optimizes_when_format_is_already_jpg()
    {
        $binary = new Binary(file_get_contents(static::FILENAME), 'image/png', 'png');
        $jpgBinary = $this->getConvertToJPGPostProcessor()->process($binary, ['quality' => 100]);
        $newJpgBinary = $this->getConvertToJPGPostProcessor()->process($jpgBinary, ['quality' => 50]);

        $this->assertEquals('image/jpeg', $newJpgBinary->getMimeType());
        $this->assertEquals('jpg', $newJpgBinary->getFormat());
        $this->assertLessThan(strlen($jpgBinary->getContent()), strlen($newJpgBinary->getContent()));
    }

    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
    }

    private function getConvertToJPGPostProcessor(): ConvertToJPGPostProcessor
    {
        return self::$container->get(ConvertToJPGPostProcessor::class);
    }
}
