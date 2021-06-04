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

use Akeneo\AssetManager\Common\Fake\InMemoryFindFileDataByFileKey;
use PHPUnit\Framework\TestCase;

class InMemoryFindFileDataByFileKeyTest extends TestCase
{
    private InMemoryFindFileDataByFileKey $findFileDataByFileKey;

    public function setUp(): void
    {
        $this->findFileDataByFileKey = new InMemoryFindFileDataByFileKey();
    }

    /**
     * @test
     */
    public function it_returns_the_image_for_a_given_file_key()
    {
        $fileData = [
            'filePath' => '0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_kartell.jpg',
            'originalFilename' => 'kartell.jpg',
        ];
        $this->findFileDataByFileKey->save($fileData);

        $imageFound = $this->findFileDataByFileKey->find($fileData['filePath']);

        $this->assertEquals($fileData, $imageFound);
    }

    /**
     * @test
     */
    public function it_returns_null_if_no_image_was_found()
    {
        $this->findFileDataByFileKey->save([
            'filePath' => '0/c/b/0/0cb0c0e115dedba676f8d1ad8343ec207ab54c7b_kartell.jpg',
            'originalFilename' => 'kartell.jpg',
        ]);

        $imageFound = $this->findFileDataByFileKey->find('foo/bar.jpg');

        $this->assertNull($imageFound);
    }
}
