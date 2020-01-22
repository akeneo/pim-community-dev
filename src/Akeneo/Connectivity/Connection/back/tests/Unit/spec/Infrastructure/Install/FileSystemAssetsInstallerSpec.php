<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Infrastructure\Install;


use Akeneo\Connectivity\Connection\Infrastructure\Install\FileSystemAssetsInstaller;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FileSystemAssetsInstallerSpec extends ObjectBehavior
{
    public function let(Filesystem $filesystem)
    {
        $this->beConstructedWith($filesystem, 'path');
    }

    public function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(FileSystemAssetsInstaller::class);
    }

    public function it_hard_copy_files($filesystem)
    {
        $filesystem->mkdir(Argument::cetera())->shouldBeCalled();
        $filesystem->mirror(Argument::cetera())->shouldBeCalled();

        $this->installAssets(false);
    }
}
