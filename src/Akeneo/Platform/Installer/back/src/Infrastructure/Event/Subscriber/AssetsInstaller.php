<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Infrastructure\Event\Subscriber;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetsInstaller
{
    public function __construct(private readonly Filesystem $filesystem, private readonly string $originDir, private readonly string $targetDir)
    {
    }

    public function installAssets(bool $shouldSymlink): void
    {
        if ($shouldSymlink) {
            $this->relativeSymlinkWithFallback();
        } else {
            $this->hardCopy();
        }
    }

    /**
     * Try to create relative symlink.
     *
     * Falling back to absolute symlink and finally hard copy.
     */
    private function relativeSymlinkWithFallback(): void
    {
        try {
            $this->symlink(true);
        } catch (IOException) {
            $this->absoluteSymlinkWithFallback();
        }
    }

    /**
     * Try to create absolute symlink.
     *
     * Falling back to hard copy.
     */
    private function absoluteSymlinkWithFallback(): void
    {
        try {
            $this->symlink();
        } catch (IOException) {
            // fall back to copy
            $this->hardCopy();
        }
    }

    /**
     * Creates symbolic link.
     *
     * @throws IOException if link can not be created
     */
    private function symlink(bool $relative = false): void
    {
        $originDir = $this->originDir;
        if ($relative) {
            $this->filesystem->mkdir(dirname($this->targetDir));
            $originDir = $this->filesystem->makePathRelative($this->originDir, realpath(dirname($this->targetDir)));
        }
        $this->filesystem->symlink($originDir, $this->targetDir);
        if (!file_exists($this->targetDir)) {
            throw new IOException(sprintf('Symbolic link "%s" was created but appears to be broken.', $this->targetDir), 0, null, $this->targetDir);
        }
    }

    /**
     * Copies origin to target.
     */
    private function hardCopy(): void
    {
        $this->filesystem->mkdir($this->targetDir, 0777);
        // We use a custom iterator to ignore VCS files
        $this->filesystem->mirror($this->originDir, $this->targetDir, Finder::create()->ignoreDotFiles(false)->in($this->originDir));
    }
}
