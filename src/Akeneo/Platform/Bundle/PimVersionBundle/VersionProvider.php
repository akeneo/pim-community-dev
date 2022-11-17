<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\PimVersionBundle;

use Akeneo\Platform\Bundle\PimVersionBundle\Version\PimVersion;

/**
 * Class VersionProvider
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VersionProvider implements VersionProviderInterface
{
    public function __construct(
        private iterable $versions,
        private string $editionCode,
        private string $projectDir
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getEdition(): string
    {
        return $this->getPimVersion()->editionName();
    }

    public function getVersion(): string
    {
        $filepath = $this->projectDir . '/version.txt';

        return file_exists($filepath) ? trim(file_get_contents($this->projectDir . '/version.txt')) : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getPatch(): string
    {
        if (!$this->isSaaSVersion()) {
            $matches = [];
            $isMatching = preg_match('/^(?P<patch>\d+\.\d+\.\d+)/', $this->getVersion(), $matches);

            if (!$isMatching) {
                return $this->getVersion();
            }

            return $matches['patch'];
        }

        return $this->getVersion();
    }

    public function getMinorVersion(): string
    {
        if (!$this->isSaaSVersion()) {
            $matches = [];
            $isMatching = preg_match('/^(?P<minor>\d+\.\d+)\.\d+/', $this->getVersion(), $matches);

            if (!$isMatching) {
                return $this->getVersion();
            }

            return $matches['minor'];
        }

        return $this->getVersion();
    }

    /**
     * {@inheritdoc}
     */
    public function getFullVersion(): string
    {
        return sprintf(
            '%s %s %s',
            $this->getEdition(),
            $this->getVersion(),
            $this->getPimVersion()->versionCodename()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isSaaSVersion(): bool
    {
        return $this->getPimVersion()->isSaas();
    }

    private function getPimVersion(): PimVersion
    {
        foreach ($this->versions as $version) {
            if ($version->isEditionCode($this->editionCode)) {
                return $version;
            }
        }

        throw new \RuntimeException("No Pim Version found.");
    }
}
