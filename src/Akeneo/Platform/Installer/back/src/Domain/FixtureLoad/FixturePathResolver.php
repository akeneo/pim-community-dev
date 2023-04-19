<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Domain\FixtureLoad;

final class FixturePathResolver
{
    /**
     * @param string[] $bundles
     */
    public static function resolve(string $catalogPath, array $bundles): string
    {
        $installerDataDir = null;

        // TODO this do not occurs to remove
        if (preg_match('/^(?P<bundle>\w+):(?P<directory>\w+)$/', $catalogPath, $matches)) {
            $reflection = new \ReflectionClass($bundles[$matches['bundle']]);
            $installerDataDir = dirname($reflection->getFilename()).'/Resources/fixtures/'.$matches['directory'];
        } else {
            $installerDataDir = $catalogPath;
        }

        if (!is_dir($installerDataDir)) {
            throw new \RuntimeException('Installer data directory cannot be found.');
        }

        if (DIRECTORY_SEPARATOR !== substr($installerDataDir, -1, 1)) {
            $installerDataDir .= DIRECTORY_SEPARATOR;
        }

        return $installerDataDir;
    }
}
