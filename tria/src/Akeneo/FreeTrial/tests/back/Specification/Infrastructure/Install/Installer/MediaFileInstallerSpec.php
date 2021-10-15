<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\FreeTrial\Infrastructure\Install\Reader\FixtureReader;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;

final class MediaFileInstallerSpec extends ObjectBehavior
{
    public function let(FixtureReader $fixtureReader, BulkSaverInterface $saver)
    {
        $this->beConstructedWith($fixtureReader, $saver, 2);
    }

    public function it_successfully_installs_media_files($fixtureReader, $saver)
    {
        $mediaFileData1 = [
            'code' => 'a/7/d/1/a7d1d2e24f0b248c4861c22fd13c74aa3fd51b1f_reddrill.png',
            'original_filename' => 'reddrill.png',
            'mime_type' => 'image/png',
            'size' => 663308,
            'extension' => 'png',
            'hash' => '673503d59d81bfac5a4cb793bfa41b5a6ab13bb5'
        ];
        $mediaFileData2 = [
            'code' => '9/1/f/d/91fde270ebeccb838fbc9b4e7578c457a732131d_drillkit.png',
            'original_filename' => 'drillkit.png',
            'mime_type' => 'image/png',
            'size' => 1640975,
            'extension' => 'png',
            'hash' => 'b699806d8505e301081fe07640a2cd1d2c409bb6'
        ];
        $mediaFileData3 = [
            'code' => '8/0/6/6/8066384297d5580febdaeed48d07e3d806f0ac61_drillbitset.png',
            'original_filename' => 'drillbitset.png',
            'mime_type' => 'image/png',
            'size' => 759201,
            'extension' => 'png',
            'hash' => 'a2d46bd36125e1aa5a57e4d2e249a6d4f96cfd8f'
        ];

        $fixtureReader->read()->willReturn(new \ArrayIterator([$mediaFileData1, $mediaFileData2, $mediaFileData3]));

        $mediaFile1 = new FileInfo();
        $mediaFile1
            ->setKey('a/7/d/1/a7d1d2e24f0b248c4861c22fd13c74aa3fd51b1f_reddrill.png')
            ->setOriginalFilename('reddrill.png')
            ->setMimeType('image/png')
            ->setSize(663308)
            ->setExtension('png')
            ->setHash('673503d59d81bfac5a4cb793bfa41b5a6ab13bb5')
            ->setStorage(FileStorage::CATALOG_STORAGE_ALIAS);

        $mediaFile2 = new FileInfo();
        $mediaFile2
            ->setKey('9/1/f/d/91fde270ebeccb838fbc9b4e7578c457a732131d_drillkit.png')
            ->setOriginalFilename('drillkit.png')
            ->setMimeType('image/png')
            ->setSize(1640975)
            ->setExtension('png')
            ->setHash('b699806d8505e301081fe07640a2cd1d2c409bb6')
            ->setStorage(FileStorage::CATALOG_STORAGE_ALIAS);

        $mediaFile3 = new FileInfo();
        $mediaFile3
            ->setKey('8/0/6/6/8066384297d5580febdaeed48d07e3d806f0ac61_drillbitset.png')
            ->setOriginalFilename('drillbitset.png')
            ->setMimeType('image/png')
            ->setSize(759201)
            ->setExtension('png')
            ->setHash('a2d46bd36125e1aa5a57e4d2e249a6d4f96cfd8f')
            ->setStorage(FileStorage::CATALOG_STORAGE_ALIAS);

        $saver->saveAll([$mediaFile1, $mediaFile2])->shouldBeCalled();
        $saver->saveAll([$mediaFile3])->shouldBeCalled();

        $this->install();
    }
}
