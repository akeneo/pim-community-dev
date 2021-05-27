<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\Persistence\InMemory;

use Akeneo\AssetManager\Common\Fake\InMemoryFileExists;
use PHPUnit\Framework\TestCase;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFileExistsTest extends TestCase
{
    private InMemoryFileExists $fileExists;

    public function setUp(): void
    {
        $this->fileExists = new InMemoryFileExists();
        $this->fileExists->save('files/kartell.jpg');
    }

    /**
     * @test
     */
    public function it_returns_true_if_the_file_exists()
    {
        $fileExists = $this->fileExists->exists('files/kartell.jpg');

        $this->assertTrue($fileExists);
    }

    /**
     * @test
     */
    public function it_returns_false_if_no_file_was_found()
    {
        $fileExists = $this->fileExists->exists('files/no_file.jpg');

        $this->assertFalse($fileExists);
    }
}
