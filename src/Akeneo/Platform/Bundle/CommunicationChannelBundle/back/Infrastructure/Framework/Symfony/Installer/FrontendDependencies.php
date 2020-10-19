<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CommunicationChannelBundle\back\Infrastructure\Framework\Symfony\Installer;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * To backport this feature, two dependencies are mandatory:
 * - @types/react-dom
 * - react-markdown
 *
 * To make it work, the package.json of the standard edition have to contain thse two libraries.
 * Therefore, it does it on the fly, by modifying the package.json if needed.
 *
 * As the installation of the assets is a post install/update command of composer, it will be triggered before updating the frontend dependencies.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FrontendDependencies
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

    public function addRequiredDependencies(): void
    {
        $packagePath = $this->projectDir . '/package.json';
        $packageFileContent = file_get_contents($packagePath);

        if (false === $packageFileContent) {
            return;
        }

        $package = \json_decode($packageFileContent, true);
        if (null === $package) {
            return;
        }

        $dependencyAdded = false;

        if (!isset($package['dependencies']['@types/react-dom']) && !isset($package['devDependencies']['@types/react-dom'])) {
            $package['dependencies']['@types/react-dom'] = '^16.8.0';
            $dependencyAdded = true;
        }
        if (!isset($package['dependencies']['react-markdown']) && !isset($package['devDependencies']['react-markdown'])) {
            $package['dependencies']['react-markdown'] = '^4.3.1';
            $dependencyAdded = true;
        }
        if (!isset($package['dependencies']['styled-components']) && !isset($package['devDependencies']['styled-components'])) {
            $package['dependencies']['styled-components'] = '^4.3.2';
            $dependencyAdded = true;
        }
        if (!isset($package['dependencies']['@types/styled-components']) && !isset($package['devDependencies']['@types/styled-components'])) {
            $package['dependencies']['@types/styled-components'] = '^4.1.18';
            $dependencyAdded = true;
        }

        if ($dependencyAdded) {
            $encodedPackage = json_encode($package, JSON_PRETTY_PRINT);
            $this->filesystem->dumpFile($packagePath, $encodedPackage);
            $this->filesystem->remove($this->projectDir . '/yarn.lock');
        }
    }
}
