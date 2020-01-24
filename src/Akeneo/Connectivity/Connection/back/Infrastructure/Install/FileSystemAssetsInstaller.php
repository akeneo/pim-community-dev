<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Install;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FileSystemAssetsInstaller implements AssetsInstaller
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $projectDir;

    public function __construct(Filesystem $filesystem, string $projectDir)
    {
        $this->filesystem = $filesystem;
        $this->projectDir = $projectDir;
    }

    public function installAssets(bool $shouldSymlink): void
    {
        $originDir = __DIR__ . '/../../../front/src';
        $targetDir = $this->projectDir . '/public/bundles/akeneoconnectivityconnection-react';
        if ($shouldSymlink) {
            $this->relativeSymlinkWithFallback($originDir, $targetDir);
        } else {
            $this->hardCopy($originDir, $targetDir);
        }
    }

    /**
     * Try to create relative symlink.
     *
     * Falling back to absolute symlink and finally hard copy.
     *
     * @param string $originDir
     * @param string $targetDir
     */
    private function relativeSymlinkWithFallback(string $originDir, string $targetDir): void
    {
        try {
            $this->symlink($originDir, $targetDir, true);
        } catch (IOException $e) {
            $this->absoluteSymlinkWithFallback($originDir, $targetDir);
        }
    }

    /**
     * Try to create absolute symlink.
     *
     * Falling back to hard copy.
     *
     * @param string $originDir
     * @param string $targetDir
     */
    private function absoluteSymlinkWithFallback(string $originDir, string $targetDir): void
    {
        try {
            $this->symlink($originDir, $targetDir);
        } catch (IOException $e) {
            // fall back to copy
            $this->hardCopy($originDir, $targetDir);
        }
    }

    /**
     * Creates symbolic link.
     *
     * @param string $originDir
     * @param string $targetDir
     * @param bool   $relative
     *
     * @throws IOException if link can not be created
     */
    private function symlink(string $originDir, string $targetDir, bool $relative = false): void
    {
        if ($relative) {
            $this->filesystem->mkdir(dirname($targetDir));

            $startPath = realpath(dirname($targetDir));
            if (false === $startPath) {
                throw new IOException(sprintf('The directory `%s` does not exist', $startPath));
            }

            $originDir = $this->filesystem->makePathRelative($originDir, $startPath);
        }
        $this->filesystem->symlink($originDir, $targetDir);
        if (!file_exists($targetDir)) {
            throw new IOException(
                sprintf('Symbolic link "%s" was created but appears to be broken.', $targetDir),
                0,
                null,
                $targetDir
            );
        }
    }

    /**
     * Copies origin to target.
     *
     * @param string $originDir
     * @param string $targetDir
     */
    private function hardCopy(string $originDir, string $targetDir): void
    {
        $this->filesystem->mkdir($targetDir, 0777);
        // We use a custom iterator to ignore VCS files
        $this->filesystem->mirror($originDir, $targetDir, Finder::create()->ignoreDotFiles(false)->in($originDir));
    }
}
